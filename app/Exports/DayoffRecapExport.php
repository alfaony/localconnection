<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DayoffRecapExport implements FromArray, WithHeadings, WithTitle
{
    protected $recap;

    public function __construct(array $recap)
    {
        $this->recap = $recap;
    }

    public function title(): string
    {
        return 'Rekap Cuti';
    }

    public function array(): array
    {
        return collect($this->recap)->map(function($row) {
            return array_merge([
                $row['user'],
                $row['type'],
                $row['quote'],
                $row['remaining'],
                $row['used'],
            ], array_values($row['months']));
        })->toArray();
    }

    public function headings(): array
    {
        return array_merge(
            ['Nama User', 'Tipe', 'Kuota', 'Sisa', 'Total Digunakan'],
            collect(range(1, 12))->map(fn($m) => \Carbon\Carbon::create()->month($m)->format('F'))->toArray()
        );
    }
}
