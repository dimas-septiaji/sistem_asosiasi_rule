@extends('layouts.app')

@section('page_title', $analysis->name)

@section('breadcrumbs')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
    <span>/</span>
    <a href="{{ route('analyses.index') }}" class="hover:text-slate-600">Riwayat Analisis</a>
    <span>/</span>
    <span class="text-slate-600 font-medium truncate max-w-xs">{{ $analysis->name }}</span>
@endsection

@section('topbar_actions')
    <!-- Export Actions -->
    <a href="{{ route('analyses.export.excel', $analysis) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-xs">
        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Export Excel
    </a>
    <a href="{{ route('analyses.export.pdf', $analysis) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-xs">
        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        Export PDF
    </a>
    <a href="{{ route('analyses.print', $analysis) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-xs">
        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Cetak Laporan
    </a>
@endsection

@section('content')
<div class="space-y-6" x-data="{ currentTab: '{{ $tab }}' }">
    <!-- Analysis Summary Header Card -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold text-slate-900">{{ $analysis->name }}</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ ucfirst($analysis->status) }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-2 font-mono">
                    <span>Dataset: <a href="{{ route('datasets.show', $analysis->dataset) }}" class="text-blue-600 hover:underline font-medium">{{ $analysis->dataset ? $analysis->dataset->name : '-' }}</a></span>
                    <span>•</span>
                    <span>Algoritma: <strong class="text-slate-800">Apriori</strong></span>
                    <span>•</span>
                    <span>Durasi: <strong class="text-slate-800">{{ $analysis->duration_ms ?? 0 }} ms</strong></span>
                    <span>•</span>
                    <span>Tanggal: <strong class="text-slate-800">{{ $analysis->created_at->format('d M Y, H:i') }}</strong></span>
                </div>
            </div>

            <!-- Parameters Badge Cards -->
            <div class="grid grid-cols-3 gap-2.5 shrink-0 text-center">
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200/60">
                    <p class="text-[10px] font-medium text-slate-500 uppercase">Min Support</p>
                    <p class="text-sm font-bold text-slate-900 font-mono mt-0.5">{{ round($analysis->min_support * 100, 2) }}%</p>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200/60">
                    <p class="text-[10px] font-medium text-slate-500 uppercase">Min Confidence</p>
                    <p class="text-sm font-bold text-slate-900 font-mono mt-0.5">{{ round($analysis->min_confidence * 100, 2) }}%</p>
                </div>
                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200/60">
                    <p class="text-[10px] font-medium text-slate-500 uppercase">Min Lift</p>
                    <p class="text-sm font-bold text-slate-900 font-mono mt-0.5">{{ $analysis->min_lift ? round($analysis->min_lift, 2) : '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 flex space-x-6">
        <button type="button" @click="currentTab = 'overview'" 
                :class="currentTab === 'overview' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="py-3 px-1 border-b-2 text-xs transition-colors flex items-center gap-2">
            <span>Ringkasan (Overview)</span>
        </button>

        <button type="button" @click="currentTab = 'itemsets'" 
                :class="currentTab === 'itemsets' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="py-3 px-1 border-b-2 text-xs transition-colors flex items-center gap-2">
            <span>Frequent Itemset</span>
            <span class="px-1.5 py-0.2 rounded text-[10px] bg-slate-100 text-slate-600 font-mono">{{ $analysis->total_frequent_itemsets }}</span>
        </button>

        <button type="button" @click="currentTab = 'rules'" 
                :class="currentTab === 'rules' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="py-3 px-1 border-b-2 text-xs transition-colors flex items-center gap-2">
            <span>Association Rule</span>
            <span class="px-1.5 py-0.2 rounded text-[10px] bg-blue-50 text-blue-700 font-mono font-semibold">{{ $analysis->total_rules }}</span>
        </button>

        <button type="button" @click="currentTab = 'iterations'" 
                :class="currentTab === 'iterations' ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="py-3 px-1 border-b-2 text-xs transition-colors flex items-center gap-2">
            <span>Detail Iterasi (C & L)</span>
            <span class="px-1.5 py-0.2 rounded text-[10px] bg-slate-100 text-slate-600 font-mono">{{ $iterations->count() }} iterasi</span>
        </button>
    </div>

    <!-- TAB 1: OVERVIEW -->
    <div x-show="currentTab === 'overview'" class="space-y-6" x-cloak>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Total Transaksi Dianalisis</p>
                <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 font-mono">{{ number_format($analysis->total_transactions) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Digunakan sebagai denominator perhitungan Support</p>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Frequent Itemset Terbentuk</p>
                <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 font-mono">{{ number_format($analysis->total_frequent_itemsets) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Kombinasi produk yang memenuhi Support ≥ {{ round($analysis->min_support * 100, 1) }}%</p>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs">
                <p class="text-xs font-medium text-slate-500">Aturan Asosiasi (Rules) Valid</p>
                <p class="mt-2 text-2xl font-bold tracking-tight text-blue-600 font-mono">{{ number_format($analysis->total_rules) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Aturan yang memenuhi Confidence ≥ {{ round($analysis->min_confidence * 100, 1) }}%</p>
            </div>
        </div>

        <!-- Metric Details & Parameter Summary -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-900">Rincian Eksekusi & Parameter Algoritma</h3>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-xs">
                    <div>
                        <dt class="font-medium text-slate-400">Algoritma</dt>
                        <dd class="mt-1 font-semibold text-slate-800">Apriori (Pure PHP Implementation)</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-400">Dataset Acuan</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $analysis->dataset ? $analysis->dataset->name : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-400">Waktu Eksekusi</dt>
                        <dd class="mt-1 font-mono text-slate-800">{{ $analysis->duration_ms }} ms</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-400">Status Proses</dt>
                        <dd class="mt-1 font-medium text-emerald-700 capitalize">{{ $analysis->status }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-400">Minimum Support</dt>
                        <dd class="mt-1 font-mono font-semibold text-slate-800">{{ round($analysis->min_support * 100, 2) }}% ({{ $analysis->min_support }})</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-400">Minimum Confidence</dt>
                        <dd class="mt-1 font-mono font-semibold text-slate-800">{{ round($analysis->min_confidence * 100, 2) }}% ({{ $analysis->min_confidence }})</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-400">Minimum Lift</dt>
                        <dd class="mt-1 font-mono font-semibold text-slate-800">{{ $analysis->min_lift ? round($analysis->min_lift, 4) : 'Tidak difilter' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-400">Tahapan Iterasi</dt>
                        <dd class="mt-1 font-mono font-semibold text-slate-800">{{ $iterations->count() }} Tingkatan ($k$)</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- TAB 2: FREQUENT ITEMSET -->
    <div x-show="currentTab === 'itemsets'" class="space-y-4" x-cloak>
        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('analyses.show', $analysis) }}" class="flex-1 flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="itemsets">

                <!-- Size Filter -->
                <div class="w-full sm:w-44">
                    <select name="size" onchange="this.form.submit()"
                            class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 text-slate-800">
                        <option value="">-- Semua Ukuran (k) --</option>
                        @foreach ($availableSizes as $sz)
                            <option value="{{ $sz }}" {{ $itemsetSize == $sz ? 'selected' : '' }}>
                                {{ $sz }}-Itemset
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Item -->
                <div class="relative flex-1 min-w-[200px]">
                    <input type="text" name="item_search" value="{{ $itemSearch }}" placeholder="Cari nama produk dalam itemset..." 
                           class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 text-slate-800">
                </div>

                <button type="submit" class="px-3 py-1.5 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg">
                    Filter
                </button>
                @if ($itemsetSize || $itemSearch)
                    <a href="{{ route('analyses.show', [$analysis, 'tab' => 'itemsets']) }}" class="px-2 py-1.5 text-xs text-slate-500 hover:text-slate-800">Reset</a>
                @endif
            </form>

            <div class="text-xs text-slate-500">
                Total: <span class="font-semibold text-slate-800">{{ $itemsets->total() }}</span> itemset lolos
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if ($itemsets->isEmpty())
                <div class="p-8 text-center text-slate-400">
                    <p class="text-xs">Tidak ada frequent itemset yang memenuhi kriteria pencarian.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/75 text-slate-500 border-b border-slate-100 font-medium">
                            <tr>
                                <th class="px-5 py-3">Kombinasi Item / Produk</th>
                                <th class="px-4 py-3 text-center">Ukuran Itemset (k)</th>
                                <th class="px-4 py-3 text-right">Support Count</th>
                                <th class="px-5 py-3 text-right">Support (%)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach ($itemsets as $itemset)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-5 py-3">
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($itemset->items as $item)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-800 border border-slate-200/60">
                                                    {{ $item->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-50 text-slate-600 border border-slate-200 font-mono">
                                            {{ $itemset->itemset_size }}-item
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-800 font-medium">
                                        {{ number_format($itemset->support_count) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono font-bold text-slate-900">
                                        {{ round($itemset->support * 100, 2) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 border-t border-slate-100">
                    {{ $itemsets->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 3: ASSOCIATION RULE -->
    <div x-show="currentTab === 'rules'" class="space-y-4" x-cloak>
        <!-- Filter & Sorting Bar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs space-y-3">
            <form method="GET" action="{{ route('analyses.show', $analysis) }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="rules">

                <!-- Search rule item -->
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="rule_search" value="{{ $ruleSearch }}" placeholder="Cari item di antecedent / consequent..." 
                           class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-1 focus:ring-blue-500 text-slate-800">
                </div>

                <!-- Sort By -->
                <div class="w-36">
                    <select name="sort_by" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800">
                        <option value="confidence" {{ $sortBy === 'confidence' ? 'selected' : '' }}>Sort: Confidence</option>
                        <option value="support" {{ $sortBy === 'support' ? 'selected' : '' }}>Sort: Support</option>
                        <option value="lift" {{ $sortBy === 'lift' ? 'selected' : '' }}>Sort: Lift Ratio</option>
                    </select>
                </div>

                <!-- Sort Dir -->
                <div class="w-28">
                    <select name="sort_dir" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800">
                        <option value="desc" {{ $sortDir === 'desc' ? 'selected' : '' }}>Tertinggi</option>
                        <option value="asc" {{ $sortDir === 'asc' ? 'selected' : '' }}>Terendah</option>
                    </select>
                </div>

                <!-- Filter Min Conf -->
                <div class="w-32">
                    <input type="number" step="0.1" name="filter_min_conf" value="{{ $filterMinConf }}" placeholder="Min Conf %"
                           class="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-mono">
                </div>

                <!-- Filter Min Lift -->
                <div class="w-28">
                    <input type="number" step="0.01" name="filter_min_lift" value="{{ $filterMinLift }}" placeholder="Min Lift"
                           class="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-mono">
                </div>

                <button type="submit" class="px-3.5 py-1.5 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Terapkan
                </button>

                @if ($ruleSearch || $filterMinConf || $filterMinLift || $sortBy !== 'confidence')
                    <a href="{{ route('analyses.show', [$analysis, 'tab' => 'rules']) }}" class="px-2 py-1.5 text-xs text-slate-500 hover:text-slate-800">Reset</a>
                @endif
            </form>
        </div>

        <!-- Rules Table -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if ($rules->isEmpty())
                <div class="p-8 text-center text-slate-400">
                    <p class="text-xs">Tidak ada aturan asosiasi yang memenuhi kriteria filter.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/75 text-slate-500 border-b border-slate-100 font-medium">
                            <tr>
                                <th class="px-4 py-3 text-center">No</th>
                                <th class="px-5 py-3">Antecedent (Jika Beli...)</th>
                                <th class="px-3 py-3 text-center">→</th>
                                <th class="px-5 py-3">Consequent (Maka Beli...)</th>
                                <th class="px-4 py-3 text-right">Support</th>
                                <th class="px-4 py-3 text-right">Confidence</th>
                                <th class="px-4 py-3 text-right">Lift</th>
                                <th class="px-4 py-3 text-center">Interpretasi</th>
                                <th class="px-5 py-3">Keterangan / Penjelasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach ($rules as $index => $rule)
                                @php
                                    $liftInfo = $rule->lift_interpretation;
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-4 py-3 text-center font-mono text-slate-400 text-[11px]">
                                        {{ $rules->firstItem() + $index }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($rule->antecedentItemset->items as $item)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-800 border border-slate-200">
                                                    {{ $item->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-center text-slate-400 font-bold">→</td>
                                    <td class="px-5 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($rule->consequentItemset->items as $item)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-blue-800 border border-blue-200">
                                                    {{ $item->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-800">
                                        {{ round($rule->support * 100, 2) }}%
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-blue-600">
                                        {{ round($rule->confidence * 100, 2) }}%
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">
                                        {{ round($rule->lift, 4) }}
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium border {{ $liftInfo['badge_class'] }}">
                                            {{ $liftInfo['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-[11px] text-slate-600 max-w-xs">
                                        {{ $rule->explanation }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 border-t border-slate-100">
                    {{ $rules->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 4: DETAIL ITERASI -->
    <div x-show="currentTab === 'iterations'" class="space-y-6" x-cloak>
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs">
            <h3 class="text-sm font-semibold text-slate-900">Prinsip Algoritma & Eliminasi Apriori</h3>
            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                Algoritma Apriori menggunakan sifat <em>Apriori Property</em>: <strong>Seluruh subset dari suatu frequent itemset haruslah frequent</strong>. Jika suatu itemset ukuran $k-1$ tidak memenuhi Minimum Support, maka seluruh kandidat superset ukuran $k$ yang memuat itemset tersebut langsung dieliminasi (pruned) tanpa perlu memindai seluruh transaksi database berulang kali.
            </p>
        </div>

        <div class="space-y-4">
            @foreach ($iterations as $iter)
                <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50/75 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center font-mono">
                                {{ $iter->k }}
                            </span>
                            <div>
                                <h4 class="text-xs font-semibold text-slate-900">
                                    Iterasi Ke-{{ $iter->k }} (Itemset Ukuran {{ $iter->k }})
                                </h4>
                                <span class="text-[11px] text-slate-400 font-mono">C{{ $iter->k }} → L{{ $iter->k }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 text-xs font-mono">
                            <span class="text-slate-500">Kandidat (C{{ $iter->k }}): <strong class="text-slate-800">{{ $iter->candidate_count }}</strong></span>
                            <span class="text-slate-300">•</span>
                            <span class="text-slate-500">Lolos (L{{ $iter->k }}): <strong class="text-emerald-700">{{ $iter->frequent_count }}</strong></span>
                            <span class="text-slate-300">•</span>
                            <span class="text-slate-400">{{ $iter->duration_ms }} ms</span>
                        </div>
                    </div>

                    <div class="p-5">
                        @php
                            $iterItemsets = $analysis->itemsets()->with('items')->where('itemset_size', $iter->k)->orderByDesc('support')->get();
                        @endphp

                        @if ($iterItemsets->isEmpty())
                            <p class="text-xs text-slate-400 italic">Tidak ada kandidat yang memenuhi ambang Minimum Support pada iterasi ini. Proses pembentukan iterasi berikutnya dihentikan.</p>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                @foreach ($iterItemsets as $is)
                                    <div class="p-2.5 rounded-lg border border-slate-200/70 bg-slate-50/50 flex items-center justify-between text-xs">
                                        <div class="flex flex-wrap gap-1 font-medium text-slate-800">
                                            {{ $is->items->pluck('name')->implode(', ') }}
                                        </div>
                                        <div class="text-right font-mono text-[11px] shrink-0 ml-2">
                                            <span class="font-bold text-blue-600">{{ round($is->support * 100, 1) }}%</span>
                                            <span class="text-slate-400 block text-[10px]">({{ $is->support_count }} tx)</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
