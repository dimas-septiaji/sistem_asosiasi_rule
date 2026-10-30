<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Services\DatasetImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatasetController extends Controller
{
    public function __construct(
        protected DatasetImportService $importService
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');

        $datasets = Dataset::withCount('transactions', 'items', 'analyses')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('original_filename', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('datasets.index', compact('datasets', 'search'));
    }

    public function create()
    {
        return view('datasets.create');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:15360',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ], [
            'file.required' => 'File dataset harus diunggah.',
            'file.max' => 'Ukuran file maksimal adalah 15MB.',
            'name.required' => 'Nama dataset wajib diisi.',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalFilename = $file->getClientOriginalName();

        // Validate allowed extensions
        if (!in_array($extension, ['csv', 'txt', 'xlsx', 'xls', 'ods', ''])) {
            return back()->withInput()->with('error', 'Format file tidak didukung. Harap unggah file CSV atau Excel (XLSX, XLS).');
        }

        // Store temporarily in private storage with original extension preserved
        $safeName = \Illuminate\Support\Str::uuid() . '.' . ($extension ?: 'csv');
        $tempPath = $file->storeAs('temp_imports', $safeName);
        $absolutePath = Storage::path($tempPath);

        try {
            $previewData = $this->importService->previewFile($absolutePath, $extension);
            $guessedMapping = $this->importService->guessMapping($previewData['headers']);

            return view('datasets.preview', [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'tempPath' => $tempPath,
                'extension' => $extension,
                'originalFilename' => $originalFilename,
                'headers' => $previewData['headers'],
                'rows' => $previewData['rows'],
                'totalRows' => $previewData['total_rows'],
                'guessedMapping' => $guessedMapping,
            ]);
        } catch (\Throwable $e) {
            Storage::delete($tempPath);
            return back()->withInput()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'temp_path' => 'required|string',
            'extension' => 'required|string',
            'original_filename' => 'required|string',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'mapping.transaction_id' => 'required|string',
            'mapping.item' => 'required|string',
            'mapping.quantity' => 'nullable|string',
            'mapping.transaction_date' => 'nullable|string',
        ], [
            'mapping.transaction_id.required' => 'Pilih kolom untuk ID Transaksi.',
            'mapping.item.required' => 'Pilih kolom untuk Nama Produk / Item.',
        ]);

        $tempPath = $request->input('temp_path');
        $extension = $request->input('extension');
        $absolutePath = Storage::path($tempPath);

        if (!Storage::exists($tempPath)) {
            return redirect()->route('datasets.create')->with('error', 'Sesi unggah telah berakhir. Silakan pilih file kembali.');
        }

        try {
            $dataset = $this->importService->import(
                $absolutePath,
                $extension,
                $request->input('name'),
                $request->input('description'),
                $request->input('original_filename'),
                $request->input('mapping'),
                auth()->id()
            );

            Storage::delete($tempPath);

            return redirect()->route('datasets.show', $dataset)
                ->with('success', "Dataset '{$dataset->name}' berhasil diimpor! Total {$dataset->total_transactions} transaksi dan {$dataset->total_items} produk unik.");
        } catch (\DomainException $e) {
            // Validation errors with specific line numbers
            Storage::delete($tempPath);
            return redirect()->route('datasets.create')
                ->withInput()
                ->with('validation_errors', explode("\n", $e->getMessage()));
        } catch (\Throwable $e) {
            Storage::delete($tempPath);
            return redirect()->route('datasets.create')
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengimpor: ' . $e->getMessage());
        }
    }

    public function show(Request $request, Dataset $dataset)
    {
        $search = $request->input('search');

        $items = $dataset->items()
            ->withCount('transactions')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $analyses = $dataset->analyses()->orderByDesc('created_at')->get();

        return view('datasets.show', compact('dataset', 'items', 'analyses', 'search'));
    }

    public function destroy(Dataset $dataset)
    {
        $name = $dataset->name;
        $dataset->delete();

        return redirect()->route('datasets.index')->with('success', "Dataset '{$name}' dan riwayat analisis terkait berhasil dihapus.");
    }

    public function downloadTemplate(): StreamedResponse
    {
        $content = $this->importService->getTemplateCsvContent();

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'template_transaksi_market_basket.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
