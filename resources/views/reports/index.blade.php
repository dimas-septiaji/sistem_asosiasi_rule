@extends('layouts.app')

@section('page_title', 'Laporan Analisis')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
    <span>/</span>
    <span class="text-slate-600 font-medium">Laporan</span>
@endsection

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('reports.index') }}" class="flex-1 max-w-md flex items-center gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama analisis..." 
                   class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 text-slate-800">
            <button type="submit" class="px-3 py-1.5 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg whitespace-nowrap">Cari</button>
            @if ($search)
                <a href="{{ route('reports.index') }}" class="px-2 py-1.5 text-xs text-slate-500 hover:text-slate-800 whitespace-nowrap">Reset</a>
            @endif
        </form>

        <div class="text-xs text-slate-500">
            Tersedia: <span class="font-semibold text-slate-800">{{ $analyses->total() }}</span> laporan analisis selesai
        </div>
    </div>

    <!-- Reports Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if ($analyses->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-xs font-medium text-slate-700">Belum ada laporan analisis yang selesai</p>
                <p class="text-[11px] mt-1">Jalankan analisis Apriori terlebih dahulu untuk mengekspor dokumen laporan.</p>
                <a href="{{ route('analyses.create') }}" class="mt-3 inline-block px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Mulai Analisis
                </a>
            </div>
        @else
            <!-- Mobile: Card list -->
            <div class="sm:hidden divide-y divide-slate-100">
                @foreach ($analyses as $an)
                    <div class="px-4 py-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <a href="{{ route('analyses.show', $an) }}" class="text-xs font-semibold text-slate-900 hover:text-blue-600 block truncate">
                                    {{ $an->name }}
                                </a>
                                <p class="text-[11px] text-slate-400 mt-0.5 truncate">{{ $an->dataset ? $an->dataset->name : '-' }}</p>
                                <div class="flex flex-wrap items-center gap-2 mt-1 text-[11px] font-mono text-slate-500">
                                    <span>Supp: {{ round($an->min_support * 100, 1) }}%</span>
                                    <span>·</span>
                                    <span>Conf: {{ round($an->min_confidence * 100, 1) }}%</span>
                                    <span>·</span>
                                    <span class="text-blue-600 font-semibold">{{ number_format($an->total_rules) }} rules</span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    Selesai: {{ $an->completed_at ? $an->completed_at->format('d M Y, H:i') : $an->created_at->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3 pt-2 border-t border-slate-100">
                            <a href="{{ route('analyses.export.pdf', $an) }}" 
                               class="flex-1 inline-flex items-center justify-center gap-1 px-2.5 py-1.5 text-[11px] font-medium bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-md transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                PDF
                            </a>

                            <a href="{{ route('analyses.export.excel', $an) }}" 
                               class="flex-1 inline-flex items-center justify-center gap-1 px-2.5 py-1.5 text-[11px] font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-md transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Excel
                            </a>

                            <a href="{{ route('analyses.print', $an) }}" target="_blank"
                               class="flex-1 inline-flex items-center justify-center gap-1 px-2.5 py-1.5 text-[11px] font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-md transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Cetak
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop: Table -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/75 text-slate-500 border-b border-slate-100 font-medium">
                        <tr>
                            <th class="px-5 py-3">Nama Analisis</th>
                            <th class="px-4 py-3">Dataset</th>
                            <th class="px-3 py-3 text-right">Support</th>
                            <th class="px-3 py-3 text-right">Confidence</th>
                            <th class="px-3 py-3 text-right">Rules</th>
                            <th class="px-4 py-3">Tanggal Selesai</th>
                            <th class="px-5 py-3 text-right">Ekspor & Cetak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach ($analyses as $an)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-3.5 font-medium text-slate-900">
                                    <a href="{{ route('analyses.show', $an) }}" class="hover:text-blue-600">
                                        {{ $an->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3.5 text-slate-500">
                                    {{ $an->dataset ? $an->dataset->name : '-' }}
                                </td>
                                <td class="px-3 py-3.5 text-right font-mono text-slate-700">{{ round($an->min_support * 100, 1) }}%</td>
                                <td class="px-3 py-3.5 text-right font-mono text-slate-700">{{ round($an->min_confidence * 100, 1) }}%</td>
                                <td class="px-3 py-3.5 text-right font-mono font-medium text-blue-600">{{ number_format($an->total_rules) }}</td>
                                <td class="px-4 py-3.5 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                                    {{ $an->completed_at ? $an->completed_at->format('d M Y, H:i') : $an->created_at->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('analyses.export.pdf', $an) }}" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-medium bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-md transition-colors" title="Download PDF">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            PDF
                                        </a>

                                        <a href="{{ route('analyses.export.excel', $an) }}" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-md transition-colors" title="Download Excel">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Excel
                                        </a>

                                        <a href="{{ route('analyses.print', $an) }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-md transition-colors" title="Print Laporan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            Cetak
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 sm:px-5 py-3 border-t border-slate-100">
                {{ $analyses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
