@extends('layouts.app')

@section('page_title', 'Data Transaksi')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
    <span>/</span>
    <span class="text-slate-600 font-medium">Data Transaksi</span>
@endsection

@section('content')
<div class="space-y-4">
    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-col gap-3">
            <div class="flex flex-col sm:flex-row gap-2">
                <!-- Filter Dataset -->
                <div class="w-full sm:w-56 shrink-0">
                    <select name="dataset_id" onchange="this.form.submit()"
                            class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 text-slate-800">
                        <option value="">-- Semua Dataset --</option>
                        @foreach ($datasets as $ds)
                            <option value="{{ $ds->id }}" {{ $datasetId == $ds->id ? 'selected' : '' }}>
                                {{ $ds->name }} ({{ number_format($ds->total_transactions) }} tx)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Transaction Code -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode transaksi (cth: TR001)..."
                           class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 text-slate-800">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg whitespace-nowrap">
                        Filter
                    </button>
                    @if ($datasetId || $search)
                        <a href="{{ route('transactions.index') }}" class="px-2 py-1.5 text-xs text-slate-500 hover:text-slate-800 whitespace-nowrap">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="text-xs text-slate-500 mt-2 sm:mt-0">
            Ditemukan: <span class="font-semibold text-slate-800">{{ $transactions->total() }}</span> transaksi
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if ($transactions->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="text-xs font-medium text-slate-700">Tidak ada transaksi ditemukan</p>
                <p class="text-[11px] mt-1">Coba sesuaikan kata kunci pencarian atau pilih dataset lain.</p>
            </div>
        @else
            <!-- Mobile: Card list -->
            <div class="sm:hidden divide-y divide-slate-100">
                @foreach ($transactions as $tx)
                    <div class="px-4 py-3">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <span class="font-mono font-semibold text-xs text-slate-900">{{ $tx->transaction_code }}</span>
                            <div class="text-right shrink-0">
                                <span class="text-[11px] text-slate-500">{{ $tx->transaction_at ? $tx->transaction_at->format('d M Y') : '-' }}</span>
                                <span class="text-[11px] font-mono text-slate-700 block">{{ $tx->items->count() }} item</span>
                            </div>
                        </div>
                        @if ($tx->dataset)
                            <p class="text-[11px] text-slate-400 mb-1.5">{{ $tx->dataset->name }}</p>
                        @endif
                        <div class="flex flex-wrap gap-1">
                            @foreach ($tx->items as $item)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] bg-slate-100 text-slate-700 border border-slate-200/60">
                                    <span>{{ $item->name }}</span>
                                    @if ((float) $item->pivot->quantity > 1)
                                        <span class="text-[10px] text-slate-400 font-mono">({{ round($item->pivot->quantity) }}x)</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- Desktop: Table -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/75 text-slate-500 border-b border-slate-100 font-medium">
                        <tr>
                            <th class="px-5 py-3">Kode Transaksi</th>
                            <th class="px-4 py-3">Dataset</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-3 py-3 text-right">Jml Item</th>
                            <th class="px-5 py-3">Produk / Barang yang Dibeli</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach ($transactions as $tx)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-medium text-slate-900">
                                    {{ $tx->transaction_code }}
                                </td>
                                <td class="px-4 py-3.5 text-slate-500">
                                    {{ $tx->dataset ? $tx->dataset->name : '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                                    {{ $tx->transaction_at ? $tx->transaction_at->format('d M Y') : '-' }}
                                </td>
                                <td class="px-3 py-3.5 text-right font-mono text-slate-800 font-medium">
                                    {{ $tx->items->count() }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-wrap gap-1.5 max-w-xl">
                                        @foreach ($tx->items as $item)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] bg-slate-100 text-slate-700 border border-slate-200/60">
                                                <span>{{ $item->name }}</span>
                                                @if ((float) $item->pivot->quantity > 1)
                                                    <span class="text-[10px] text-slate-400 font-mono">({{ round($item->pivot->quantity) }}x)</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 sm:px-5 py-3 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
