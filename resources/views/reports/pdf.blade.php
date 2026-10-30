<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Market Basket Analysis - {{ $analysis->name }}</title>
    <style>
        @page {
            margin: 25mm 20mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            color: #0f172a;
            margin: 0 0 4px 0;
            font-weight: bold;
        }
        .header .meta {
            font-size: 10px;
            color: #64748b;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-top: 18px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .params-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .params-table td {
            padding: 5px 8px;
            font-size: 10px;
        }
        .params-table .label {
            width: 25%;
            color: #64748b;
            font-weight: 500;
        }
        .params-table .value {
            width: 25%;
            color: #0f172a;
            font-weight: bold;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.data-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-align: left;
            padding: 6px 8px;
            border-bottom: 1px solid #cbd5e1;
            font-size: 10px;
        }
        table.data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
        }
        .badge-positive { background-color: #ecfdf5; color: #047857; }
        .badge-negative { background-color: #fff1f2; color: #be123c; }
        .badge-neutral { background-color: #f1f5f9; color: #475569; }
        .conclusion-box {
            background-color: #f8fafc;
            border-left: 3px solid #2563eb;
            padding: 10px 14px;
            margin-top: 15px;
            font-size: 10px;
            color: #334155;
        }
        .footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="footer">
        Laporan Market Basket Analysis • Dihasilkan secara otomatis oleh sistem pada {{ date('d M Y H:i:s') }}
    </div>

    <!-- Header -->
    <div class="header">
        <h1>LAPORAN MARKET BASKET ANALYSIS (APRIORI)</h1>
        <div class="meta">
            Analisis: <strong>{{ $analysis->name }}</strong> &nbsp;|&nbsp;
            Dataset: <strong>{{ $analysis->dataset ? $analysis->dataset->name : '-' }}</strong> &nbsp;|&nbsp;
            Tanggal: <strong>{{ $analysis->created_at->format('d F Y, H:i') }}</strong>
        </div>
    </div>

    <!-- Parameter Ringkasan -->
    <div class="section-title">1. Parameter & Ringkasan Eksekusi</div>
    <table class="params-table">
        <tr>
            <td class="label">Minimum Support:</td>
            <td class="value">{{ round($analysis->min_support * 100, 2) }}% ({{ $analysis->min_support }})</td>
            <td class="label">Total Transaksi:</td>
            <td class="value">{{ number_format($analysis->total_transactions) }} transaksi</td>
        </tr>
        <tr>
            <td class="label">Minimum Confidence:</td>
            <td class="value">{{ round($analysis->min_confidence * 100, 2) }}% ({{ $analysis->min_confidence }})</td>
            <td class="label">Frequent Itemset:</td>
            <td class="value">{{ number_format($analysis->total_frequent_itemsets) }} pola</td>
        </tr>
        <tr>
            <td class="label">Minimum Lift:</td>
            <td class="value">{{ $analysis->min_lift ? round($analysis->min_lift, 4) : 'Tidak difilter' }}</td>
            <td class="label">Association Rules:</td>
            <td class="value">{{ number_format($analysis->total_rules) }} aturan valid</td>
        </tr>
        <tr>
            <td class="label">Durasi Analisis:</td>
            <td class="value">{{ $analysis->duration_ms }} ms</td>
            <td class="label">Algoritma:</td>
            <td class="value">Apriori (Pure Engine)</td>
        </tr>
    </table>

    <!-- Frequent Itemsets -->
    <div class="section-title">2. Pola Pembelian Sering (Frequent Itemset)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th>Kombinasi Item / Produk</th>
                <th class="text-center" style="width: 15%;">Ukuran (k)</th>
                <th class="text-right" style="width: 20%;">Support Count</th>
                <th class="text-right" style="width: 20%;">Support (%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($itemsets as $idx => $itemset)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $itemset->items->pluck('name')->implode(', ') }}</strong></td>
                    <td class="text-center">{{ $itemset->itemset_size }}-item</td>
                    <td class="text-right">{{ $itemset->support_count }}</td>
                    <td class="text-right"><strong>{{ round($itemset->support * 100, 2) }}%</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #94a3b8;">Tidak ada itemset yang memenuhi minimum support.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Association Rules -->
    <div class="section-title" style="page-break-before: auto;">3. Aturan Asosiasi (Association Rules)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th>Antecedent (Jika Beli...)</th>
                <th style="width: 4%; text-align: center;">→</th>
                <th>Consequent (Maka Beli...)</th>
                <th class="text-right" style="width: 12%;">Support</th>
                <th class="text-right" style="width: 14%;">Confidence</th>
                <th class="text-right" style="width: 12%;">Lift</th>
                <th class="text-center" style="width: 14%;">Interpretasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rules as $idx => $rule)
                @php
                    $liftVal = round($rule->lift, 4);
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $rule->antecedentItemset->items->pluck('name')->implode(', ') }}</td>
                    <td class="text-center" style="color: #64748b;">→</td>
                    <td><strong>{{ $rule->consequentItemset->items->pluck('name')->implode(', ') }}</strong></td>
                    <td class="text-right">{{ round($rule->support * 100, 2) }}%</td>
                    <td class="text-right" style="color: #2563eb;"><strong>{{ round($rule->confidence * 100, 2) }}%</strong></td>
                    <td class="text-right"><strong>{{ $liftVal }}</strong></td>
                    <td class="text-center">
                        @if ($liftVal > 1)
                            <span class="badge badge-positive">Positif</span>
                        @elseif ($liftVal < 1)
                            <span class="badge badge-negative">Negatif</span>
                        @else
                            <span class="badge badge-neutral">Independen</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="color: #94a3b8;">Tidak ada aturan asosiasi yang memenuhi kriteria ambang batas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Kesimpulan Aktual -->
    <div class="section-title">4. Kesimpulan & Penjelasan Hasil</div>
    <div class="conclusion-box">
        <p style="margin: 0 0 6px 0; font-weight: bold; color: #0f172a;">Ringkasan Temuan:</p>
        <ul style="margin: 0; padding-left: 18px;">
            <li>Ditemukan sebanyak <strong>{{ $analysis->total_rules }} aturan asosiasi</strong> yang memenuhi kriteria Minimum Support {{ round($analysis->min_support * 100, 1) }}% dan Minimum Confidence {{ round($analysis->min_confidence * 100, 1) }}%.</li>
            @if ($rules->isNotEmpty())
                @php
                    $topRule = $rules->first();
                    $topAnt = $topRule->antecedentItemset->items->pluck('name')->implode(', ');
                    $topCon = $topRule->consequentItemset->items->pluck('name')->implode(', ');
                @endphp
                <li>Aturan dengan confidence tertinggi adalah <strong>{{ $topAnt }} → {{ $topCon }}</strong> dengan Confidence <strong>{{ round($topRule->confidence * 100, 2) }}%</strong> dan Lift <strong>{{ round($topRule->lift, 4) }}</strong>.</li>
                <li>Pernyataan asosiasi: <em>{{ $topRule->explanation }}</em></li>
            @else
                <li>Tidak ditemukan aturan yang lolos pada ambang batas parameter ini. Pertimbangkan untuk menurunkan nilai Minimum Support atau Minimum Confidence untuk menemukan pola yang lebih tersebar.</li>
            @endif
        </ul>
    </div>
</body>
</html>
