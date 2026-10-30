@extends('layouts.app')

@section('page_title', 'Konfigurasi Analisis Apriori')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
    <span>/</span>
    <a href="{{ route('analyses.index') }}" class="hover:text-slate-600">Analisis</a>
    <span>/</span>
    <span class="text-slate-600 font-medium">Baru</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-900">Parameter Analisis Apriori</h2>
            <p class="text-xs text-slate-500 mt-0.5">Tentukan ambang batas Minimum Support dan Minimum Confidence untuk ekstraksi aturan asosiasi.</p>
        </div>

        <form method="POST" action="{{ route('analyses.store') }}" class="p-6 space-y-5">
            @csrf

            <!-- Analysis Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">
                    Nama Analisis <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', 'Analisis MBA - ' . date('d M Y H:i')) }}" required
                       placeholder="Contoh: Analisis Transaksi Kuartal 1"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900">
            </div>

            <!-- Select Dataset -->
            <div>
                <label for="dataset_id" class="block text-xs font-semibold text-slate-700 mb-1">
                    Pilih Dataset Transaksi <span class="text-rose-500">*</span>
                </label>
                <select name="dataset_id" id="dataset_id" required
                        class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900">
                    <option value="">-- Pilih Dataset --</option>
                    @foreach ($datasets as $ds)
                        <option value="{{ $ds->id }}" {{ (old('dataset_id', $selectedDatasetId) == $ds->id) ? 'selected' : '' }}>
                            {{ $ds->name }} ({{ number_format($ds->transactions_count) }} Transaksi, {{ number_format($ds->items_count) }} Item Unik)
                        </option>
                    @endforeach
                </select>
                @if ($datasets->isEmpty())
                    <p class="text-[11px] text-amber-600 mt-1">
                        Belum ada dataset. <a href="{{ route('datasets.create') }}" class="underline font-medium">Unggah dataset terlebih dahulu</a>.
                    </p>
                @endif
            </div>

            <!-- Parameters Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <!-- Minimum Support (%) -->
                <div>
                    <label for="min_support" class="block text-xs font-semibold text-slate-700 mb-1">
                        Minimum Support (%) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0.01" max="100" name="min_support" id="min_support" 
                               value="{{ old('min_support', '20') }}" required
                               class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono pr-8">
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-slate-400 font-medium">%</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Batas minimum frekuensi kemunculan kombinasi item dalam total transaksi (contoh: 20%).
                    </p>
                </div>

                <!-- Minimum Confidence (%) -->
                <div>
                    <label for="min_confidence" class="block text-xs font-semibold text-slate-700 mb-1">
                        Minimum Confidence (%) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0.01" max="100" name="min_confidence" id="min_confidence" 
                               value="{{ old('min_confidence', '60') }}" required
                               class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono pr-8">
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-slate-400 font-medium">%</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Tingkat kepastian bahwa jika item A dibeli, item B juga dibeli (contoh: 60%).
                    </p>
                </div>
            </div>

            <!-- Minimum Lift (Optional) -->
            <div>
                <label for="min_lift" class="block text-xs font-semibold text-slate-700 mb-1">
                    Minimum Lift Ratio <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <input type="number" step="0.01" min="0.01" name="min_lift" id="min_lift" 
                       value="{{ old('min_lift') }}"
                       placeholder="Kosongkan jika tidak ingin menyaring berdasarkan Lift"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono">
                <p class="text-[11px] text-slate-400 mt-1">
                    Nilai Lift &gt; 1 menunjukkan asosiasi positif. Jika dikosongkan, seluruh rule yang lolos Confidence akan disimpan.
                </p>
            </div>

            <!-- Submit Action -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('analyses.index') }}" class="px-4 py-2 text-xs font-medium text-slate-600 hover:text-slate-800">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-xs" {{ $datasets->isEmpty() ? 'disabled' : '' }}>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Mulai Proses Apriori
                </button>
            </div>
        </form>
    </div>

    <!-- Algorithm Guide Card -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs space-y-3 text-xs text-slate-600">
        <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wider">Panduan Parameter Apriori</h3>
        <p class="leading-relaxed">
            Algoritma Apriori akan membentuk kandidat itemset secara bertahap mulai dari $k=1$, lalu mengeliminasi itemset yang tidak memenuhi <strong class="text-slate-800">Minimum Support</strong>.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-[11px] pt-1">
            <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200/60">
                <p class="font-semibold text-slate-800">Support(A)</p>
                <p class="text-slate-500 mt-1">Frekuensi transaksi memuat item A dibagi total transaksi.</p>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200/60">
                <p class="font-semibold text-slate-800">Confidence(A → B)</p>
                <p class="text-slate-500 mt-1">Peluang B dibeli jika transaksi memuat A: $Support(A \cup B) / Support(A)$.</p>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200/60">
                <p class="font-semibold text-slate-800">Lift(A → B)</p>
                <p class="text-slate-500 mt-1">Kekuatan aturan dibanding pembelian acak: $Confidence / Support(B)$.</p>
            </div>
        </div>
    </div>
</div>
@endsection
