<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\Dataset;
use App\Services\AprioriService;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function __construct(
        protected AprioriService $aprioriService
    ) {}

    public function index(Request $request)
    {
        $datasetId = $request->input('dataset_id');
        $status = $request->input('status');
        $search = $request->input('search');

        $datasets = Dataset::orderBy('name')->get();

        $analyses = Analysis::with('dataset')
            ->when($datasetId, fn($q) => $q->where('dataset_id', $datasetId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('analyses.index', compact('analyses', 'datasets', 'datasetId', 'status', 'search'));
    }

    public function create(Request $request)
    {
        $selectedDatasetId = $request->input('dataset_id');
        $datasets = Dataset::withCount('transactions', 'items')->orderBy('name')->get();

        return view('analyses.create', compact('datasets', 'selectedDatasetId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'dataset_id' => 'required|exists:datasets,id',
            'min_support' => 'required|numeric|min:0.01|max:100',
            'min_confidence' => 'required|numeric|min:0.01|max:100',
            'min_lift' => 'nullable|numeric|min:0.001',
        ], [
            'name.required' => 'Nama analisis wajib diisi.',
            'dataset_id.required' => 'Pilih dataset yang akan dianalisis.',
            'dataset_id.exists' => 'Dataset yang dipilih tidak valid.',
            'min_support.required' => 'Minimum Support wajib diisi.',
            'min_support.min' => 'Minimum Support harus lebih besar dari 0%.',
            'min_support.max' => 'Minimum Support maksimal 100%.',
            'min_confidence.required' => 'Minimum Confidence wajib diisi.',
            'min_confidence.min' => 'Minimum Confidence harus lebih besar dari 0%.',
            'min_confidence.max' => 'Minimum Confidence maksimal 100%.',
            'min_lift.min' => 'Minimum Lift harus bernilai positif.',
        ]);

        $dataset = Dataset::findOrFail($request->input('dataset_id'));

        if ($dataset->transactions()->count() === 0) {
            return back()->withInput()->with('error', 'Dataset tidak memiliki data transaksi.');
        }

        // Convert percentage input (e.g. 20%) to decimal ratio (0.20)
        $minSupportRatio = (float) $request->input('min_support') / 100.0;
        $minConfidenceRatio = (float) $request->input('min_confidence') / 100.0;
        $minLiftRatio = $request->filled('min_lift') ? (float) $request->input('min_lift') : null;

        $analysis = Analysis::create([
            'dataset_id' => $dataset->id,
            'user_id' => auth()->id(),
            'name' => $request->input('name'),
            'algorithm' => 'apriori',
            'min_support' => $minSupportRatio,
            'min_confidence' => $minConfidenceRatio,
            'min_lift' => $minLiftRatio,
            'status' => 'pending',
            'total_transactions' => $dataset->transactions()->count(),
        ]);

        try {
            $this->aprioriService->run($analysis);

            return redirect()->route('analyses.show', $analysis)
                ->with('success', "Analisis '{$analysis->name}' selesai! Ditemukan {$analysis->total_frequent_itemsets} frequent itemset dan {$analysis->total_rules} association rule.");
        } catch (\Throwable $e) {
            return redirect()->route('analyses.show', $analysis)
                ->with('error', 'Analisis gagal dijalankan: ' . $e->getMessage());
        }
    }

    public function destroy(Analysis $analysis)
    {
        $name = $analysis->name;
        $analysis->delete();

        return redirect()->route('analyses.index')
            ->with('success', "Riwayat analisis '{$name}' berhasil dihapus.");
    }
}
