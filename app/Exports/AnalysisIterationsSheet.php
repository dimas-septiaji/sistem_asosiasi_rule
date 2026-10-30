<?php

namespace App\Exports;

use App\Models\Analysis;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class AnalysisIterationsSheet implements FromArray, WithTitle
{
    public function __construct(protected Analysis $analysis) {}

    public function array(): array
    {
        $rows = [
            ['Iterasi (k)', 'Kandidat (Ck)', 'Frequent Itemset (Lk)', 'Durasi (ms)']
        ];

        $iterations = $this->analysis->iterations()->get();
        foreach ($iterations as $iter) {
            $rows[] = [
                "Iterasi {$iter->k} (k={$iter->k})",
                $iter->candidate_count,
                $iter->frequent_count,
                $iter->duration_ms,
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Detail Iterasi';
    }
}
