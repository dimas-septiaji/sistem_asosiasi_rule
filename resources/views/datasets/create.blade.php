@extends('layouts.app')

@section('page_title', 'Unggah Dataset')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
    <span>/</span>
    <a href="{{ route('datasets.index') }}" class="hover:text-slate-600">Dataset</a>
    <span>/</span>
    <span class="text-slate-600 font-medium">Unggah</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Card Form -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-900">Form Unggah Dataset Transaksi</h2>
            <p class="text-xs text-slate-500 mt-0.5">Unggah file berformat CSV atau Excel (XLSX) untuk memulai proses analisis Apriori.</p>
        </div>

        <form method="POST" action="{{ route('datasets.preview') }}" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf

            <!-- Dataset Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">
                    Nama Dataset <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       placeholder="Contoh: Transaksi Ritel Januari 2026"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-700 mb-1">
                    Deskripsi <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <textarea name="description" id="description" rows="3"
                          placeholder="Catatan sumber data, periode penjualan, atau karakteristik transaksi..."
                          class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900">{{ old('description') }}</textarea>
            </div>

            <!-- File Upload Input -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    File Dataset (CSV / XLSX) <span class="text-rose-500">*</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-xl hover:border-slate-300 transition-colors bg-slate-50/50">
                    <div class="space-y-2 text-center">
                        <svg class="mx-auto h-10 w-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <div class="flex text-xs text-slate-600 justify-center">
                            <label for="file" class="relative cursor-pointer font-medium text-blue-600 hover:text-blue-500 focus-within:outline-hidden">
                                <span>Pilih file dari komputer</span>
                                <input id="file" name="file" type="file" accept=".csv,.txt,.xlsx,.xls" class="sr-only" required>
                            </label>
                        </div>
                        <p class="text-[11px] text-slate-400">CSV, XLSX, XLS maksimal 10MB</p>
                        <div id="file-chosen" class="text-xs font-medium text-slate-800 hidden"></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('datasets.index') }}" class="px-4 py-2 text-xs font-medium text-slate-600 hover:text-slate-800">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-xs">
                    Lanjut: Pratinjau & Pemetaan Kolom →
                </button>
            </div>
        </form>
    </div>

    <!-- Requirements & Format Card -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wider">Format Standar File</h3>
            <a href="{{ route('datasets.template') }}" class="text-xs font-medium text-blue-600 hover:underline inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download Contoh Template (.csv)
            </a>
        </div>
        <p class="text-xs text-slate-500">
            Gunakan format <strong class="text-slate-700">long-format</strong>, di mana satu baris mewakili satu produk yang ada dalam suatu transaksi keranjang belanja.
        </p>
        <div class="bg-slate-900 text-slate-200 p-3 rounded-lg text-[11px] font-mono overflow-x-auto leading-relaxed">
transaction_id,item,quantity,transaction_date<br>
TR001,Roti,2,2026-01-01<br>
TR001,Susu,1,2026-01-01<br>
TR002,Roti,1,2026-01-01<br>
TR002,Mentega,2,2026-01-01<br>
TR003,Roti,1,2026-01-02
        </div>
        <ul class="text-[11px] text-slate-500 space-y-1 list-disc list-inside">
            <li><strong class="text-slate-700">transaction_id</strong> dan <strong class="text-slate-700">item</strong> wajib diisi.</li>
            <li>Item yang sama pada transaksi yang sama akan digabung dan dijumlahkan kuantitasnya.</li>
            <li>Algoritma Apriori akan menghitung keberadaan item unik per transaksi (binary frequency).</li>
        </ul>
    </div>
</div>

<script>
    document.getElementById('file').addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : '';
        var display = document.getElementById('file-chosen');
        if (fileName) {
            display.textContent = 'File dipilih: ' + fileName;
            display.classList.remove('hidden');
        } else {
            display.classList.add('hidden');
        }
    });
</script>
@endsection
