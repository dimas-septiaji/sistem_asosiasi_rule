<?php

namespace App\Exports;

use App\Models\Analysis;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class AnalysisOverviewSheet implements FromArray, WithTitle
{
    public function __construct(protected Analysis $analysis) {}

    public function array(): array
    {
        return [
            ['Parameter / Metrik', 'Nilai'],
            ['Nama Analisis', $this->analysis->name],
            ['Dataset', $this->analysis->dataset ? $this->analysis->dataset->name : '-'],
            ['Tanggal Analisis', $this->analysis->created_at ? $this->analysis->created_at->format('Y-m-d H:i:s') : '-'],
            ['Minimum Support', ($this->analysis->min_support * 100) . '%'],
            ['Minimum Confidence', ($this->analysis->min_confidence * 100) . '%'],
            ['Minimum Lift', $this->analysis->min_lift ? $this->analysis->min_lift : 'Tidak difilter'],
            ['Total Transaksi', $this->analysis->total_transactions],
            ['Total Frequent Itemset', $this->analysis->total_frequent_itemsets],
            ['Total Association Rule', $this->analysis->total_rules],
            ['Durasi Analisis (ms)', $this->analysis->duration_ms ?? '-'],
            ['Status', ucfirst($this->analysis->status)],
        ];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }
}
