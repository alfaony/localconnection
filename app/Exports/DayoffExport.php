<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class DayoffExport implements FromCollection, WithHeadings
{
    protected $cutis;

    public function __construct($cutis)
    {
        $this->cutis = $cutis;
    }

    public function collection()
    {
        return $this->cutis->map(function ($cuti) {
            return [
                $cuti->user->name,
                $cuti->type->name,
                Carbon::parse($cuti->date_start)->format('d-m-Y'),
                Carbon::parse($cuti->date_end)->format('d-m-Y'),
                $cuti->durationInDays() . ' hari',
                $cuti->reason,
                $cuti->statusText,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Pegawai',
            'Jenis Cuti',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Durasi',
            'Alasan',
            'Status',
        ];
    }
}
