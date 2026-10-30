<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Laporan - {{ $analysis->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11pt; }
            .print-page { max-width: 100% !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 py-8">
    <!-- Top toolbar on screen -->
    <div class="max-w-4xl mx-auto mb-6 px-4 flex items-center justify-between no-print">
        <a href="{{ route('analyses.show', $analysis) }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 hover:text-slate-900">
            ← Kembali ke Hasil Analisis
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Sekarang (Ctrl+P)
            </button>
        </div>
    </div>

    <!-- Paper Sheet Document -->
    <div class="max-w-4xl mx-auto bg-white p-10 rounded-xl shadow-xs border border-slate-200 print-page space-y-6">
        <!-- Header -->
        <div class="border-b-2 border-blue-600 pb-4">
            <h1 class="text-xl font-bold text-slate-900 uppercase">Laporan Market Basket Analysis</h1>
            <p class="text-xs text-slate-500 mt-1 font-mono">
                Analisis: <strong class="text-slate-800">{{ $analysis->name }}</strong> • 
                Dataset: <strong class="text-slate-800">{{ $analysis->dataset ? $analysis->dataset->name : '-' }}</strong> • 
                Dicetak: {{ date('d M Y, H:i') }}
            </p>
        </div>

        <!-- Param Table -->
        <div>
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2">1. Parameter & Ringkasan Eksekusi</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-lg bg-slate-50 border border-slate-200/80 text-xs font-mono">
                <div>
                    <span class="text-slate-400 block text-[10px]">MIN SUPPORT</span>
                    <strong class="text-slate-900">{{ round($analysis->min_support * 100, 2) }}%</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">MIN CONFIDENCE</span>
                    <strong class="text-slate-900">{{ round($analysis->min_confidence * 100, 2) }}%</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">TOTAL TRANSAKSI</span>
                    <strong class="text-slate-900">{{ number_format($analysis->total_transactions) }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">ATURAN VALID</span>
                    <strong class="text-blue-600">{{ number_format($analysis->total_rules) }} Rules</strong>
                </div>
            </div>
        </div>

        <!-- Frequent Itemset -->
        <div>
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2">2. Frequent Itemsets Terbentuk ({{ $itemsets->count() }})</h2>
            <table class="w-full text-left text-xs border border-slate-200">
                <thead class="bg-slate-50 text-slate-600 font-medium">
                    <tr>
                        <th class="p-2 border border-slate-200">Kombinasi Item</th>
                        <th class="p-2 text-center border border-slate-200 w-24">Ukuran (k)</th>
                        <th class="p-2 text-right border border-slate-200 w-32">Support Count</th>
                        <th class="p-2 text-right border border-slate-200 w-28">Support (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse ($itemsets as $itemset)
                        <tr>
                            <td class="p-2 border border-slate-200 font-medium">{{ $itemset->items->pluck('name')->implode(', ') }}</td>
                            <td class="p-2 border border-slate-200 text-center font-mono">{{ $itemset->itemset_size }}</td>
                            <td class="p-2 border border-slate-200 text-right font-mono">{{ $itemset->support_count }}</td>
                            <td class="p-2 border border-slate-200 text-right font-mono font-bold">{{ round($itemset->support * 100, 2) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-slate-400 border border-slate-200">Tidak ada frequent itemset yang lolos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Rules -->
        <div>
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2">3. Association Rules ({{ $rules->count() }})</h2>
            <table class="w-full text-left text-xs border border-slate-200">
                <thead class="bg-slate-50 text-slate-600 font-medium">
                    <tr>
                        <th class="p-2 border border-slate-200 text-center w-10">No</th>
                        <th class="p-2 border border-slate-200">Antecedent (Jika Beli...)</th>
                        <th class="p-2 border border-slate-200 text-center w-8">→</th>
                        <th class="p-2 border border-slate-200">Consequent (Maka Beli...)</th>
                        <th class="p-2 border border-slate-200 text-right w-20">Support</th>
                        <th class="p-2 border border-slate-200 text-right w-20">Confidence</th>
                        <th class="p-2 border border-slate-200 text-right w-16">Lift</th>
                        <th class="p-2 border border-slate-200">Penjelasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse ($rules as $idx => $rule)
                        <tr>
                            <td class="p-2 border border-slate-200 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                            <td class="p-2 border border-slate-200 font-medium">{{ $rule->antecedentItemset->items->pluck('name')->implode(', ') }}</td>
                            <td class="p-2 border border-slate-200 text-center text-slate-400 font-bold">→</td>
                            <td class="p-2 border border-slate-200 font-medium text-blue-700">{{ $rule->consequentItemset->items->pluck('name')->implode(', ') }}</td>
                            <td class="p-2 border border-slate-200 text-right font-mono">{{ round($rule->support * 100, 2) }}%</td>
                            <td class="p-2 border border-slate-200 text-right font-mono font-bold">{{ round($rule->confidence * 100, 2) }}%</td>
                            <td class="p-2 border border-slate-200 text-right font-mono font-bold">{{ round($rule->lift, 4) }}</td>
                            <td class="p-2 border border-slate-200 text-[11px] text-slate-600">{{ $rule->explanation }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-4 text-center text-slate-400 border border-slate-200">Tidak ada aturan yang memenuhi ambang batas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
