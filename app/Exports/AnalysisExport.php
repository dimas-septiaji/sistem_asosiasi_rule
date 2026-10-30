<?php

namespace App\Exports;

use App\Models\Analysis;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AnalysisExport implements WithMultipleSheets, Export
{
    use Exportable;

    public function __construct(
        protected Analysis $analysis
    ) {}

    public function sheets(): array
    {
        return [
            new AnalysisOverviewSheet($this->analysis),
            new AnalysisFrequentItemsetsSheet($this->analysis),
            new AnalysisRulesSheet($this->analysis),
            new AnalysisIterationsSheet($this->analysis),
        ];
    }
}
