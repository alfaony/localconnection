<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportPointProductivityExport implements WithMultipleSheets
{
    protected $reports;
    protected $startDate;
    protected $endDate;

    public function __construct($reports, $startDate, $endDate)
    {
        $this->reports   = $reports;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    public function sheets(): array
    {
        return [
            new ReportPointProductivitySummarySheet($this->reports, $this->startDate, $this->endDate),
            new ReportPointProductivityDetailSheet($this->reports, $this->startDate, $this->endDate),
        ];
    }
}