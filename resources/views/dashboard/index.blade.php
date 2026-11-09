@extends('layouts.app')

@section('page_title', 'Dashboard')

@section('breadcrumbs')
    <span>Home</span>
    <span>/</span>
    <span class="text-slate-600 font-medium">Dashboard</span>
@endsection

@section('topbar_actions')
    <a href="{{ route('datasets.create') }}" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-xs">
        <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
        </svg>
        <span class="hidden sm:inline">Upload Dataset</span>
    </a>
    <a href="{{ route('analyses.create') }}" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-xs">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <span class="hidden sm:inline">Mulai Analisis</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stat Cards (6 Key Database Metrics) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Datasets -->
        <div class="bg-white p-3 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <p class="text-xs font-medium text-slate-500">Total Dataset</p>
            <p class="mt-1.5 text-xl sm:text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalDatasets) }}</p>
            <div class="mt-1.5 flex items-center text-[11px] text-slate-400">
                <span>File terunggah</span>
            </div>
        </div>

        <!-- Transactions -->
        <div class="bg-white p-3 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <p class="text-xs font-medium text-slate-500">Total Transaksi</p>
            <p class="mt-1.5 text-xl sm:text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalTransactions) }}</p>
            <div class="mt-1.5 flex items-center text-[11px] text-slate-400">
                <span>Keranjang belanja</span>
            </div>
        </div>

        <!-- Unique Items -->
        <div class="bg-white p-3 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <p class="text-xs font-medium text-slate-500">Total Item Unik</p>
            <p class="mt-1.5 text-xl sm:text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalItems) }}</p>
            <div class="mt-1.5 flex items-center text-[11px] text-slate-400">
                <span>Varian produk</span>
            </div>
        </div>

        <!-- Analyses -->
        <div class="bg-white p-3 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <p class="text-xs font-medium text-slate-500">Total Analisis</p>
            <p class="mt-1.5 text-xl sm:text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalAnalyses) }}</p>
            <div class="mt-1.5 flex items-center text-[11px] text-slate-400">
                <span>Riwayat eksekusi</span>
            </div>
        </div>

        <!-- Frequent Itemsets -->
        <div class="bg-white p-3 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <p class="text-xs font-medium text-slate-500">Frequent Itemset</p>
            <p class="mt-1.5 text-xl sm:text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalFrequentItemsets) }}</p>
            <div class="mt-1.5 flex items-center text-[11px] text-slate-400">
                <span>Pola kombinasi</span>
            </div>
        </div>

        <!-- Association Rules -->
        <div class="bg-white p-3 sm:p-4 rounded-xl border border-slate-200/80 shadow-xs">
            <p class="text-xs font-medium text-slate-500">Association Rule</p>
            <p class="mt-1.5 text-xl sm:text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalRules) }}</p>
            <div class="mt-1.5 flex items-center text-[11px] text-slate-400">
                <span>Aturan terbentuk</span>
            </div>
        </div>
    </div>

    <!-- Main Grid: Recent Analyses & Recent Datasets -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Analyses -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Riwayat Analisis Terbaru</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Percobaan analisis Apriori yang telah dijalankan</p>
                </div>
                <a href="{{ route('analyses.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 shrink-0 ml-2">Lihat Semua →</a>
            </div>

            <div class="flex-1">
                @if ($recentAnalyses->isEmpty())
                    <div class="p-8 text-center">
                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-slate-700">Belum ada analisis yang dijalankan</p>
                        <p class="text-[11px] text-slate-400 mt-1">Jalankan algoritma Apriori untuk mengidentifikasi aturan asosiasi.</p>
                        <a href="{{ route('analyses.create') }}" class="mt-3 inline-block text-xs font-semibold text-blue-600 hover:underline">Mulai Analisis Baru</a>
                    </div>
                @else
                    <!-- Mobile: Card list -->
                    <div class="sm:hidden divide-y divide-slate-100">
                        @foreach ($recentAnalyses as $analysis)
                            <div class="px-4 py-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <a href="{{ route('analyses.show', $analysis) }}" class="text-xs font-medium text-slate-900 hover:text-blue-600 block truncate">
                                            {{ $analysis->name }}
                                        </a>
                                        <span class="text-[11px] text-slate-400 block mt-0.5 truncate">
                                            {{ $analysis->dataset ? $analysis->dataset->name : '-' }}
                                        </span>
                                        <span class="text-[11px] text-slate-500 font-mono mt-1 block">
                                            Supp: {{ round($analysis->min_support * 100, 1) }}% · Conf: {{ round($analysis->min_confidence * 100, 1) }}%
                                        </span>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                            {{ $analysis->total_rules }} rules
                                        </span>
                                        <a href="{{ route('analyses.show', $analysis) }}" class="text-xs font-medium text-blue-600 hover:underline block mt-1.5">Detail</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <!-- Desktop: Table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50/75 text-slate-500 border-b border-slate-100">
                                <tr>
                                    <th class="px-5 py-2.5 font-medium">Nama Analisis</th>
                                    <th class="px-3 py-2.5 font-medium">Min Supp / Conf</th>
                                    <th class="px-3 py-2.5 font-medium text-right">Hasil</th>
                                    <th class="px-5 py-2.5 font-medium text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach ($recentAnalyses as $analysis)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-5 py-3">
                                            <a href="{{ route('analyses.show', $analysis) }}" class="font-medium text-slate-900 hover:text-blue-600 block truncate max-w-[180px]">
                                                {{ $analysis->name }}
                                            </a>
                                            <span class="text-[11px] text-slate-400 block mt-0.5 truncate max-w-[180px]">
                                                {{ $analysis->dataset ? $analysis->dataset->name : '-' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-slate-600">
                                            <span class="font-mono text-[11px]">{{ round($analysis->min_support * 100, 1) }}%</span> /
                                            <span class="font-mono text-[11px]">{{ round($analysis->min_confidence * 100, 1) }}%</span>
                                        </td>
                                        <td class="px-3 py-3 text-right">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                                {{ $analysis->total_rules }} rules
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <a href="{{ route('analyses.show', $analysis) }}" class="text-xs font-medium text-blue-600 hover:underline">Detail</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Datasets -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Dataset Tersedia</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kumpulan data transaksi penjualan yang siap diolah</p>
                </div>
                <a href="{{ route('datasets.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 shrink-0 ml-2">Kelola Dataset →</a>
            </div>

            <div class="flex-1">
                @if ($recentDatasets->isEmpty())
                    <div class="p-8 text-center">
                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-slate-700">Belum ada dataset yang diimpor</p>
                        <p class="text-[11px] text-slate-400 mt-1">Unggah file CSV atau Excel untuk memulai analisis.</p>
                        <a href="{{ route('datasets.create') }}" class="mt-3 inline-block text-xs font-semibold text-blue-600 hover:underline">Unggah Dataset Sekarang</a>
                    </div>
                @else
                    <!-- Mobile: Card list -->
                    <div class="sm:hidden divide-y divide-slate-100">
                        @foreach ($recentDatasets as $dataset)
                            <div class="px-4 py-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <a href="{{ route('datasets.show', $dataset) }}" class="text-xs font-medium text-slate-900 hover:text-blue-600 block truncate">
                                            {{ $dataset->name }}
                                        </a>
                                        <span class="text-[11px] text-slate-400 block mt-0.5 truncate">
                                            {{ $dataset->original_filename }}
                                        </span>
                                        <span class="text-[11px] text-slate-500 font-mono mt-1 block">
                                            {{ number_format($dataset->transactions_count) }} tx · {{ number_format($dataset->items_count) }} item
                                        </span>
                                    </div>
                                    <a href="{{ route('analyses.create', ['dataset_id' => $dataset->id]) }}" class="text-xs font-medium text-blue-600 hover:underline shrink-0">Analisis</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <!-- Desktop: Table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50/75 text-slate-500 border-b border-slate-100">
                                <tr>
                                    <th class="px-5 py-2.5 font-medium">Dataset</th>
                                    <th class="px-3 py-2.5 font-medium">Transaksi</th>
                                    <th class="px-3 py-2.5 font-medium">Item Unik</th>
                                    <th class="px-5 py-2.5 font-medium text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach ($recentDatasets as $dataset)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-5 py-3">
                                            <a href="{{ route('datasets.show', $dataset) }}" class="font-medium text-slate-900 hover:text-blue-600 block truncate max-w-[180px]">
                                                {{ $dataset->name }}
                                            </a>
                                            <span class="text-[11px] text-slate-400 block mt-0.5 truncate max-w-[180px]">
                                                {{ $dataset->original_filename }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-slate-600 font-mono text-[11px]">
                                            {{ number_format($dataset->transactions_count) }}
                                        </td>
                                        <td class="px-3 py-3 text-slate-600 font-mono text-[11px]">
                                            {{ number_format($dataset->items_count) }}
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <a href="{{ route('analyses.create', ['dataset_id' => $dataset->id]) }}" class="text-xs font-medium text-blue-600 hover:underline">Analisis</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
