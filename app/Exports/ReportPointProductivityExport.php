<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportPointProductivityExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $reports;
    protected $startDate;
    protected $endDate;

    public function __construct($reports, $startDate, $endDate)
    {
        $this->reports = $reports;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return collect($this->reports);
    }

    public function headings(): array
    {
        return [
            'Perusahaan',
            'Divisi',
            'Name',
            'Poin Training',
            'Poin Hak Cipta',
            'Poin Pencapaian Penjualan',
            'Poin Tugas Harian',
            'Total Poin'
        ];
    }

    public function map($report): array
    {
        return [
            $report['company'],
            $report['division'],
            $report['name'],
            $report['training_points'],
            $report['ip_right_points'],
            $report['sales_achievement_points'],
            $report['daily_task_point'],
            $report['total_points'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Report Productivity';
    }
}