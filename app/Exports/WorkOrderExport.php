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
    protected $company_id;
    protected $division_id;

    public function __construct($company_id, $division_id = null)
    {
        $this->company_id = $company_id;
        $this->division_id = $division_id;
    }
    public function query()
    {
        $query = WorkOrder::byCompany($this->company_id)->with('quote')->orderBy('work_order_number', 'desc');
        if ($this->division_id) {
            $query->byDivision($this->division_id);
        }
        return $query;
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
