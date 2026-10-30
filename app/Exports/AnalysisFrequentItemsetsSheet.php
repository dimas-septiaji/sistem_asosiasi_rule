<?php

namespace App\Exports;

use App\Models\Analysis;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class AnalysisFrequentItemsetsSheet implements FromArray, WithTitle
{
    public function __construct(protected Analysis $analysis) {}

    public function array(): array
    {
        $rows = [
            ['No', 'Kombinasi Item', 'Ukuran (k)', 'Support Count', 'Support (%)']
        ];

        $itemsets = $this->analysis->itemsets()->with('items')->orderBy('itemset_size')->orderByDesc('support')->get();
        $no = 1;

        foreach ($itemsets as $itemset) {
            $rows[] = [
                $no++,
                $itemset->items->pluck('name')->implode(', '),
                $itemset->itemset_size,
                $itemset->support_count,
                round($itemset->support * 100, 2) . '%',
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Frequent Itemsets';
    }
}
