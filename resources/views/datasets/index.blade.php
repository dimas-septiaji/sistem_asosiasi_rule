@extends('layouts.app')

@section('page_title', 'Kelola Dataset')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
    <span>/</span>
    <span class="text-slate-600 font-medium">Dataset</span>
@endsection

@section('topbar_actions')
    <a href="{{ route('datasets.template') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-xs">
        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        Download Template CSV
    </a>
    <a href="{{ route('datasets.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-xs">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Unggah Dataset Baru
    </a>
@endsection

@section('content')
<div class="space-y-4">
    <!-- Header Filter & Search -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" action="{{ route('datasets.index') }}" class="flex-1 max-w-md flex items-center gap-2">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama dataset atau file..." 
                       class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white transition-all text-slate-800">
            </div>
            <button type="submit" class="px-3 py-1.5 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors">
                Cari
            </button>
            @if ($search)
                <a href="{{ route('datasets.index') }}" class="px-2 py-1.5 text-xs text-slate-500 hover:text-slate-800">Reset</a>
            @endif
        </form>

        <div class="text-xs text-slate-500">
            Total: <span class="font-semibold text-slate-800">{{ $datasets->total() }}</span> dataset
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if ($datasets->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-slate-800">Tidak ada dataset ditemukan</h3>
                <p class="text-xs text-slate-500 mt-1">Unggah file CSV atau Excel untuk memulai pemrosesan transaksi.</p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <a href="{{ route('datasets.template') }}" class="px-3 py-1.5 text-xs font-medium bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">
                        Download Template CSV
                    </a>
                    <a href="{{ route('datasets.create') }}" class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Unggah Dataset
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/75 text-slate-500 border-b border-slate-100 font-medium">
                        <tr>
                            <th class="px-5 py-3">Nama Dataset</th>
                            <th class="px-4 py-3">Nama File Asli</th>
                            <th class="px-3 py-3 text-right">Baris</th>
                            <th class="px-3 py-3 text-right">Transaksi</th>
                            <th class="px-3 py-3 text-right">Item Unik</th>
                            <th class="px-3 py-3 text-right">Analisis</th>
                            <th class="px-4 py-3">Tanggal Import</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach ($datasets as $dataset)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-3.5 font-medium text-slate-900">
                                    <a href="{{ route('datasets.show', $dataset) }}" class="hover:text-blue-600">
                                        {{ $dataset->name }}
                                    </a>
                                    @if ($dataset->description)
                                        <p class="text-[11px] text-slate-400 font-normal truncate max-w-xs mt-0.5">{{ $dataset->description }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-slate-500">
                                    <span class="inline-flex items-center gap-1 font-mono text-[11px]">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        {{ $dataset->original_filename }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-right font-mono text-slate-600">{{ number_format($dataset->total_rows) }}</td>
                                <td class="px-3 py-3.5 text-right font-mono font-medium text-slate-900">{{ number_format($dataset->total_transactions) }}</td>
                                <td class="px-3 py-3.5 text-right font-mono text-slate-600">{{ number_format($dataset->total_items) }}</td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                        {{ $dataset->analyses_count }}x
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-500 whitespace-nowrap">
                                    {{ $dataset->imported_at ? $dataset->imported_at->format('d M Y, H:i') : $dataset->created_at->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('analyses.create', ['dataset_id' => $dataset->id]) }}" 
                                           class="px-2.5 py-1 text-[11px] font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-md transition-colors">
                                            Analisis
                                        </a>
                                        <a href="{{ route('datasets.show', $dataset) }}" 
                                           class="px-2.5 py-1 text-[11px] font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-md transition-colors">
                                            Detail
                                        </a>
                                        <form method="POST" action="{{ route('datasets.destroy', $dataset) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dataset ini? Seluruh transaksi dan riwayat analisis terkait akan ikut terhapus.')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition-colors" title="Hapus Dataset">
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

            <div class="px-5 py-3 border-t border-slate-100">
                {{ $datasets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
