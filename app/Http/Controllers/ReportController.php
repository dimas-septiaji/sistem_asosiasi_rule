<?php

namespace App\Http\Controllers;

use App\Exports\AnalysisExport;
use App\Models\Analysis;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $analyses = Analysis::with('dataset')
            ->where('status', 'completed')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('reports.index', compact('analyses', 'search'));
    }

    public function exportPdf(Analysis $analysis)
    {
        $analysis->load(['dataset', 'iterations']);
        $itemsets = $analysis->itemsets()->with('items')->orderBy('itemset_size')->orderByDesc('support')->get();
        $rules = $analysis->associationRules()->with(['antecedentItemset.items', 'consequentItemset.items'])->orderByDesc('confidence')->get();

        $pdf = Pdf::loadView('reports.pdf', compact('analysis', 'itemsets', 'rules'))
            ->setPaper('a4', 'portrait');

        $filename = 'Laporan_MBA_' . str_replace(' ', '_', $analysis->name) . '_' . date('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Analysis $analysis)
    {
        $filename = 'Laporan_MBA_' . str_replace(' ', '_', $analysis->name) . '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new AnalysisExport($analysis), $filename);
    }

    public function print(Analysis $analysis)
    {
        $analysis->load(['dataset', 'iterations']);
        $itemsets = $analysis->itemsets()->with('items')->orderBy('itemset_size')->orderByDesc('support')->get();
        $rules = $analysis->associationRules()->with(['antecedentItemset.items', 'consequentItemset.items'])->orderByDesc('confidence')->get();

        return view('reports.print', compact('analysis', 'itemsets', 'rules'));
    }
}
