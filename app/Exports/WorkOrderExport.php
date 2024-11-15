<?php

namespace App\Exports;

use App\Models\WorkOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class WorkOrderExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldQueue
{
    public function query()
    {
        return WorkOrder::byCompany(auth()->user()->company_id)->with('quote')->orderBy('work_order_number', 'desc');
    }

    public function headings(): array
    {
        return ['Work Order Number', 'Total', 'Quote Number'];
    }

    public function map($workOrder): array
    {
        return [
            $workOrder->number_result,
            'Rp. ' . number_format($workOrder->total, 0, ',', '.'),
            optional($workOrder->quote)->number_result,
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
