<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

use Illuminate\Support\Str;
use Carbon\Carbon;

class DailyTaskExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    /**
     * Map data yang akan diekspor.
     *
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->start_date ? Carbon::parse($row->start_date)->format('d-m-Y').' - '.Carbon::parse($row->end_date)->format('d-m-Y') : "",
            $row->taskStatus->name,
            $row->head ? $row->nameShow.' < '.$row->head->name : $row->nameShow,
            $row->project ? $row->project->title : "-",
            $row->dataProject ? $row->dataProject->title : "-",
            $row->point == 0 ? "-" : $row->point,
            $row->user->name ?? '',
            $row->assign->name ?? '',
            $row->created_at,
            $row->submit ? Carbon::parse($row->submit)->format('d-m-Y') : "-",
            $row->last_complete_date ? Carbon::parse($row->last_complete_date)->format('d-m-Y') : "-",
        ];
    }

    /**
     * Definisikan heading untuk kolom di Excel
     */
    public function headings(): array
    {
        return [
            'Tanggal',
            'Status',
            'Nama Tugas',
            'Main Proyek',
            'Proyek',
            'Poin',
            'Dibuat',
            'Ditugaskan',
            'Tanggal Dibuat',
            'Tanggal Submit',
            'Tanggal Disetujui',
        ];
    }
}
