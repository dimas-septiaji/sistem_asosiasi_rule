<?php

namespace App\Exports;

use App\Models\Analysis;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class AnalysisRulesSheet implements FromArray, WithTitle
{
    public function __construct(protected Analysis $analysis) {}

    public function array(): array
    {
        $rows = [
            ['No', 'Antecedent (Jika Beli)', 'Consequent (Maka Beli)', 'Support (%)', 'Confidence (%)', 'Lift', 'Interpretasi Lift', 'Keterangan']
        ];

        $rules = $this->analysis->associationRules()
            ->with(['antecedentItemset.items', 'consequentItemset.items'])
            ->orderByDesc('confidence')
            ->orderByDesc('lift')
            ->get();

        $no = 1;
        foreach ($rules as $rule) {
            $ant = $rule->antecedentItemset ? $rule->antecedentItemset->items->pluck('name')->implode(', ') : '-';
            $con = $rule->consequentItemset ? $rule->consequentItemset->items->pluck('name')->implode(', ') : '-';
            $liftInterp = $rule->lift_interpretation['label'] ?? '-';

            $rows[] = [
                $no++,
                $ant,
                $con,
                round($rule->support * 100, 2) . '%',
                round($rule->confidence * 100, 2) . '%',
                round($rule->lift, 4),
                $liftInterp,
                $rule->explanation,
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Association Rules';
    }
}
