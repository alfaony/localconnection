<?php

namespace App\Exports;

use App\Models\Bast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BastExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldQueue
{
    public function query()
    {
        return Bast::byCompany(auth()->user()->company_id)->with('workOrder')->orderBy('date', 'desc');
    }

    public function headings(): array
    {
        return ['Date', 'BAST Number', 'Work Order Number'];
    }

    public function map($bast): array
    {
        return [
            \Carbon\Carbon::parse($bast->date)->format('d-m-Y'),
            $bast->number_result,
            optional($bast->workOrder)->number_result,
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

