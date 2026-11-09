@extends('layouts.app')

@section('page_title', 'Pratinjau & Pemetaan Kolom')

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
    <span>/</span>
    <a href="{{ route('datasets.index') }}" class="hover:text-slate-600">Dataset</a>
    <span>/</span>
    <span class="text-slate-600 font-medium">Pratinjau</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <form method="POST" action="{{ route('datasets.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="temp_path" value="{{ $tempPath }}">
        <input type="hidden" name="extension" value="{{ $extension }}">
        <input type="hidden" name="original_filename" value="{{ $originalFilename }}">
        <input type="hidden" name="name" value="{{ $name }}">
        <input type="hidden" name="description" value="{{ $description }}">

        <!-- Top summary info -->
        <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Dataset yang akan diimpor</span>
                <h2 class="text-base font-semibold text-slate-900 mt-0.5">{{ $name }}</h2>
                <p class="text-xs text-slate-500 mt-0.5 font-mono break-all sm:break-normal">{{ $originalFilename }} • Terdeteksi ~{{ number_format($totalRows) }} baris data</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('datasets.create') }}" class="px-3 py-2 text-xs font-medium text-slate-600 hover:text-slate-800 bg-slate-100 rounded-lg whitespace-nowrap">
                    Ganti File
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-xs whitespace-nowrap">
                    Konfirmasi Impor →
                </button>
            </div>
        </div>

        <!-- Column Mapping Configuration -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-900">Pemetaan Kolom (Column Mapping)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Cocokkan header kolom dari file Anda dengan kolom yang dibutuhkan sistem.</p>
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Transaction ID Mapping -->
                <div>
                    <label for="mapping_tx" class="block text-xs font-semibold text-slate-700 mb-1">
                        ID Transaksi (transaction_id) <span class="text-rose-500">*</span>
                    </label>
                    <select name="mapping[transaction_id]" id="mapping_tx" required
                            class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono">
                        <option value="">-- Pilih Kolom Header --</option>
                        @foreach ($headers as $header)
                            <option value="{{ $header }}" {{ ($guessedMapping['transaction_id'] ?? '') === $header ? 'selected' : '' }}>
                                {{ $header }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Kode faktur / invoice belanja.</p>
                </div>

                <!-- Item / Product Mapping -->
                <div>
                    <label for="mapping_item" class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Produk / Item <span class="text-rose-500">*</span>
                    </label>
                    <select name="mapping[item]" id="mapping_item" required
                            class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono">
                        <option value="">-- Pilih Kolom Header --</option>
                        @foreach ($headers as $header)
                            <option value="{{ $header }}" {{ ($guessedMapping['item'] ?? '') === $header ? 'selected' : '' }}>
                                {{ $header }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Nama barang yang dibeli.</p>
                </div>

                <!-- Quantity Mapping -->
                <div>
                    <label for="mapping_qty" class="block text-xs font-semibold text-slate-700 mb-1">
                        Kuantitas (quantity) <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <select name="mapping[quantity]" id="mapping_qty"
                            class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono">
                        <option value="">-- Abaikan (Default 1) --</option>
                        @foreach ($headers as $header)
                            <option value="{{ $header }}" {{ ($guessedMapping['quantity'] ?? '') === $header ? 'selected' : '' }}>
                                {{ $header }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Jumlah produk dibeli (jika tersedia).</p>
                </div>

                <!-- Transaction Date Mapping -->
                <div>
                    <label for="mapping_date" class="block text-xs font-semibold text-slate-700 mb-1">
                        Tanggal Transaksi (transaction_date) <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <select name="mapping[transaction_date]" id="mapping_date"
                            class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono">
                        <option value="">-- Abaikan --</option>
                        @foreach ($headers as $header)
                            <option value="{{ $header }}" {{ ($guessedMapping['transaction_date'] ?? '') === $header ? 'selected' : '' }}>
                                {{ $header }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Tanggal pembelian transaksi.</p>
                </div>
            </div>
        </div>

        <!-- Sample Rows Preview Table -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Sampel Pratinjau Data (Maks. 10 Baris Pertama)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pastikan isi data sesuai sebelum melanjutkan proses impor.</p>
                </div>
                <span class="text-[11px] text-slate-400 font-mono">Menampilkan {{ count($rows) }} baris sampel</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-slate-50/75 text-slate-600 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-2.5 font-semibold text-slate-400">#</th>
                            @foreach ($headers as $header)
                                <th class="px-4 py-2.5 font-semibold">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach ($rows as $index => $row)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-2 text-slate-400 text-[11px]">{{ $index + 1 }}</td>
                                @foreach ($headers as $header)
                                    <td class="px-4 py-2 truncate max-w-xs">{{ $row[$header] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Action -->
        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
            <a href="{{ route('datasets.create') }}" class="px-4 py-2 text-xs font-medium text-slate-600 hover:text-slate-800 text-center">
                ← Kembali
            </a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm">
                Konfirmasi & Impor ke Database
            </button>
        </div>
    </form>
</div>
@endsection
