<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class MeetingExport implements FromCollection, WithHeadings, WithMapping
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

    public function map($row): array
    {
        $type = match (strtolower($row->meeting_type ?? '')) {
            'online'      => 'Rapat Online',
            'offline'     => 'Rapat Offline',
            'google_meet' => 'Google Meet',
            default       => $row->meeting_type ?? '-',
        };

        $participants = $row->participants()
            ->pluck('name')
            ->implode(', ');

        return [
            $row->meeting_name,
            $type,
            Carbon::parse($row->start_date)->format('d-m-Y'),
            Carbon::parse($row->start_time)->format('H:i') . ' - ' . Carbon::parse($row->end_time)->format('H:i'),
            $row->meeting_location ?? '-',
            strip_tags($row->meeting_agenda ?? '-'),
            $row->user->name ?? '-',
            $participants ?: '-',
            $row->status ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Rapat',
            'Jenis Rapat',
            'Tanggal',
            'Waktu',
            'Lokasi',
            'Agenda',
            'Dibuat Oleh',
            'Peserta',
            'Status',
        ];
    }
}
