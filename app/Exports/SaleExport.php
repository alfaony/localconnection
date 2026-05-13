<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SaleExport implements WithMultipleSheets
{
    protected $filters;
    protected $companyIds;

    public function __construct($filters, $companyIds)
    {
        $this->filters    = $filters;
        $this->companyIds = $companyIds;
    }

    public function sheets(): array
    {
        return [
            new SaleDetailSheet($this->filters, $this->companyIds),
            new SaleSummarySheet($this->filters, $this->companyIds),
        ];
    }
}
