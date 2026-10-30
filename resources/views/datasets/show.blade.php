@extends('layouts.app')

@section('page_title', $dataset->name)

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
    <span>/</span>
    <a href="{{ route('datasets.index') }}" class="hover:text-slate-600">Dataset</a>
    <span>/</span>
    <span class="text-slate-600 font-medium truncate max-w-xs">{{ $dataset->name }}</span>
@endsection

@section('topbar_actions')
    <a href="{{ route('transactions.index', ['dataset_id' => $dataset->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-xs">
        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        Lihat Semua Transaksi
    </a>
    <a href="{{ route('analyses.create', ['dataset_id' => $dataset->id]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-xs">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        Jalankan Analisis Apriori
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Dataset Summary Header -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold text-slate-900">{{ $dataset->name }}</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ ucfirst($dataset->status) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                    {{ $dataset->description ?: 'Tidak ada deskripsi tambahan untuk dataset ini.' }}
                </p>
                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-400 mt-3 font-mono">
                    <span>File: <strong class="text-slate-700 font-medium">{{ $dataset->original_filename }}</strong></span>
                    <span>•</span>
                    <span>Format: <strong class="text-slate-700 font-medium uppercase">{{ $dataset->file_type }}</strong></span>
                    <span>•</span>
                    <span>Diimpor: <strong class="text-slate-700 font-medium">{{ $dataset->imported_at ? $dataset->imported_at->format('d M Y, H:i') : '-' }}</strong></span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 shrink-0">
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/60 text-center">
                    <p class="text-[11px] font-medium text-slate-500">Total Baris</p>
                    <p class="text-base font-bold text-slate-900 mt-0.5 font-mono">{{ number_format($dataset->total_rows) }}</p>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/60 text-center">
                    <p class="text-[11px] font-medium text-slate-500">Transaksi</p>
                    <p class="text-base font-bold text-blue-600 mt-0.5 font-mono">{{ number_format($dataset->total_transactions) }}</p>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/60 text-center">
                    <p class="text-[11px] font-medium text-slate-500">Item Unik</p>
                    <p class="text-base font-bold text-slate-900 mt-0.5 font-mono">{{ number_format($dataset->total_items) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Columns: Unique Items Table & Past Analyses on this Dataset -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Unique Items (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Daftar Produk / Item Unik</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Produk yang terdaftar dalam dataset dan frekuensi kemunculannya</p>
                </div>

                <form method="GET" action="{{ route('datasets.show', $dataset) }}" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama item..."
                           class="px-2.5 py-1 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 text-slate-800">
                    <button type="submit" class="px-2.5 py-1 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg">Cari</button>
                    @if ($search)
                        <a href="{{ route('datasets.show', $dataset) }}" class="text-xs text-slate-400 hover:text-slate-600">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/75 text-slate-500 border-b border-slate-100 font-medium">
                        <tr>
                            <th class="px-5 py-2.5">Nama Item / Produk</th>
                            <th class="px-4 py-2.5 text-right">Frekuensi Muncul</th>
                            <th class="px-4 py-2.5 text-right">Relatif Support</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($items as $item)
                            @php
                                $ratio = $dataset->total_transactions > 0 ? ($item->transactions_count / $dataset->total_transactions) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-5 py-2.5 font-medium text-slate-900">
                                    {{ $item->name }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono text-slate-700">
                                    {{ number_format($item->transactions_count) }} transaksi
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono text-slate-500">
                                    {{ round($ratio * 100, 2) }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-6 text-center text-slate-400">Tidak ada produk ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-slate-100">
                {{ $items->links() }}
            </div>
        </div>

        <!-- Analyses on this dataset (1 Col) -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Riwayat Analisis Dataset</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Percobaan dengan dataset ini</p>
                </div>
                <a href="{{ route('analyses.create', ['dataset_id' => $dataset->id]) }}" class="text-xs font-semibold text-blue-600 hover:underline">+ Baru</a>
            </div>

            <div class="p-0 flex-1">
                @if ($analyses->isEmpty())
                    <div class="p-8 text-center text-slate-400">
                        <p class="text-xs">Belum ada analisis untuk dataset ini.</p>
                        <a href="{{ route('analyses.create', ['dataset_id' => $dataset->id]) }}" class="mt-2 inline-block text-xs font-semibold text-blue-600 hover:underline">
                            Jalankan Analisis Sekarang
                        </a>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($analyses as $an)
                            <div class="p-4 hover:bg-slate-50/50 transition-colors flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="{{ route('analyses.show', $an) }}" class="text-xs font-semibold text-slate-900 hover:text-blue-600 truncate block">
                                        {{ $an->name }}
                                    </a>
                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5 font-mono">
                                        <span>Supp: {{ round($an->min_support * 100, 1) }}%</span>
                                        <span>•</span>
                                        <span>Conf: {{ round($an->min_confidence * 100, 1) }}%</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $an->created_at->format('d M Y, H:i') }} • {{ $an->total_rules }} rules
                                    </p>
                                </div>
                                <a href="{{ route('analyses.show', $an) }}" class="text-xs font-medium text-blue-600 hover:underline shrink-0">
                                    Hasil →
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
