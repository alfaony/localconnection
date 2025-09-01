<?php

namespace App\Exports;

use App\Models\OfficeAttendance;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        return OfficeAttendance::query()
        ->join('users', 'office_attendances.user_id', '=', 'users.id')
        ->select('office_attendances.*') // penting agar semua kolom model tetap dikenali
        ->with('user')
        ->when($this->request->filled('employee'), function ($q) {
            $q->where('user_id', $this->request->employee);
        })
        ->when($this->request->filled('start_date') && $this->request->filled('end_date'), function ($q) {
            $q->whereBetween('office_attendances.created_at', [
                $this->request->start_date,
                $this->request->end_date
            ]);
        })
        ->orderBy('users.name')
        ->orderBy('office_attendances.time');
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Hari, Tanggal',
            'Waktu',
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->user->name,
            \Carbon\Carbon::parse($attendance->created_at)->locale('id')->translatedFormat('l, d F Y'),
            \Carbon\Carbon::parse($attendance->created_at)->format('H:i:s'),
        ];
    }
}
