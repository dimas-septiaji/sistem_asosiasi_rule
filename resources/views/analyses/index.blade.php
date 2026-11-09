@extends('layouts.app')

@section('page_title', 'Riwayat Analisis')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
    <span>/</span>
    <span class="text-slate-600 font-medium">Riwayat Analisis</span>
@endsection

@section('topbar_actions')
    <a href="{{ route('analyses.create') }}" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-xs">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span class="hidden sm:inline">Mulai Analisis Baru</span>
    </a>
@endsection

@section('content')
<div class="space-y-4">
    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('analyses.index') }}" class="flex flex-col gap-3">
            <div class="flex flex-col sm:flex-row gap-2">
                <!-- Filter Dataset -->
                <div class="w-full sm:w-56 shrink-0">
                    <select name="dataset_id" onchange="this.form.submit()"
                            class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 text-slate-800">
                        <option value="">-- Semua Dataset --</option>
                        @foreach ($datasets as $ds)
                            <option value="{{ $ds->id }}" {{ $datasetId == $ds->id ? 'selected' : '' }}>
                                {{ $ds->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Status -->
                <div class="w-full sm:w-36 shrink-0">
                    <select name="status" onchange="this.form.submit()"
                            class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 text-slate-800">
                        <option value="">-- Semua Status --</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="processing" {{ $status === 'processing' ? 'selected' : '' }}>Processing</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama analisis..."
                           class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 text-slate-800">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg whitespace-nowrap">
                        Filter
                    </button>
                    @if ($datasetId || $status || $search)
                        <a href="{{ route('analyses.index') }}" class="px-2 py-1.5 text-xs text-slate-500 hover:text-slate-800 whitespace-nowrap">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="text-xs text-slate-500 mt-2">
            Total: <span class="font-semibold text-slate-800">{{ $analyses->total() }}</span> analisis
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if ($analyses->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-xs font-medium text-slate-700">Tidak ada riwayat analisis ditemukan</p>
                <p class="text-[11px] mt-1">Jalankan analisis Apriori dengan memilih dataset dan parameter support/confidence.</p>
                <a href="{{ route('analyses.create') }}" class="mt-3 inline-block px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Mulai Analisis Baru
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
                                <div class="flex items-center gap-2 mt-1">
                                    @if ($an->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Completed</span>
                                    @elseif ($an->status === 'failed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200">Failed</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">{{ ucfirst($an->status) }}</span>
                                    @endif
                                    <span class="text-[11px] text-slate-400 font-mono">{{ $an->created_at->format('d M Y') }}</span>
                                </div>
                            </div>
                            <div class="shrink-0 flex flex-col items-end gap-1.5">
                                <a href="{{ route('analyses.show', $an) }}"
                                   class="px-2 py-1 text-[11px] font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-md transition-colors">
                                    Lihat Hasil
                                </a>
                                <form method="POST" action="{{ route('analyses.destroy', $an) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hasil analisis ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition-colors" title="Hapus Riwayat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
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
                            <th class="px-3 py-3 text-right">Min Supp</th>
                            <th class="px-3 py-3 text-right">Min Conf</th>
                            <th class="px-3 py-3 text-right">Itemsets</th>
                            <th class="px-3 py-3 text-right">Rules</th>
                            <th class="px-3 py-3 text-right">Durasi</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
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
                                <td class="px-3 py-3.5 text-right font-mono text-slate-600">{{ number_format($an->total_frequent_itemsets) }}</td>
                                <td class="px-3 py-3.5 text-right font-mono font-medium text-blue-600">{{ number_format($an->total_rules) }}</td>
                                <td class="px-3 py-3.5 text-right font-mono text-[11px] text-slate-400">
                                    {{ $an->duration_ms !== null ? $an->duration_ms . ' ms' : '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if ($an->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Completed
                                        </span>
                                    @elseif ($an->status === 'failed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-rose-50 text-rose-700 border border-rose-200" title="{{ $an->error_message }}">
                                            Failed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                            {{ ucfirst($an->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                                    {{ $an->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('analyses.show', $an) }}"
                                           class="px-2.5 py-1 text-[11px] font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-md transition-colors">
                                            Lihat Hasil
                                        </a>
                                        <form method="POST" action="{{ route('analyses.destroy', $an) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hasil analisis ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition-colors" title="Hapus Riwayat">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
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
