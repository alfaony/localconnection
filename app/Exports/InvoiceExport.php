<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Auth;

class InvoiceExport implements FromQuery, WithMapping, WithHeadings, ShouldQueue, WithChunkReading
{
    protected $filters;
    protected $company_id;

    public function __construct($filters, $company_id)
    {
        $this->filters = $filters;
        $this->company_id = $company_id;
    }

    public function query()
    {
        // Apply filters to the Invoice model
        $query = Invoice::query()
            ->byCompany($this->company_id); // Always filter by company ID

        // Apply filters for `number_result`
        // if (!empty($this->filters['number_result'])) {
        //     $query->where('number_result', 'like', '%' . $this->filters['number_result'] . '%');
        // }

        // Apply filters for `reference`
        // if (!empty($this->filters['reference'])) {
        //     $query->where('reference', 'like', '%' . $this->filters['reference'] . '%');
        // }

        // Apply date range filters for `start_date` and `end_date`
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) 
        {
            // $query->whereBetween('start_date', [$this->filters['start_date'], $this->filters['end_date']]);
            $query->$query = Invoice::query()
            ->byCompany(Auth::user()->company_id); // Always filter by company ID
        
        // Apply filters for `number_result`
        if (!empty($this->filters['number_result'])) {
            $query->where('number_result', 'like', '%' . $this->filters['number_result'] . '%');
        }
        
        // Apply filters for `reference`
        if (!empty($this->filters['reference'])) {
            $query->where('reference', 'like', '%' . $this->filters['reference'] . '%');
        }
        
        // Apply date range filters for `start_date` and `end_date`
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            // $query->whereBetween('start_date', [$this->filters['start_date'], $this->filters['end_date']]);
            $query->byDateRange($this->filters['start_date'], $this->filters['end_date']);
        }
        
        // Apply filters for `customer_name`
        // if (!empty($this->filters['customer_name'])) {
        //     $query->whereHas('customer', function ($q) {
        //         $q->where('name', 'like', '%' . $this->filters['customer_name'] . '%');
        //     });
        // }
        
        // Apply filters for `status`
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        
        // Apply search logic for `number_result` and BAST relation
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where('number_result', 'LIKE', "%{$search}%")
                ->orWhereHas('bast', function ($q) use ($search) {
                    $q->where('number_result', 'LIKE', "%{$search}%");
                });
        }
        
        // Order the results based on the `created_at` column
        if (!empty($this->filters['order'])) {
            $query->orderBy('created_at', $this->filters['order']);
        }
        
        return $query;
        
        }

        // Apply filters for `customer_name`
        if (!empty($this->filters['customer_name'])) {
            $query->whereHas('customer', function ($q) {
                $q->where('name', 'like', '%' . $this->filters['customer_name'] . '%');
            });
        }

        // Apply filters for `status`
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        // Apply search logic for `number_result` and BAST relation
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where('number_result', 'LIKE', "%{$search}%")
                ->orWhereHas('bast', function ($q) use ($search) {
                    $q->where('number_result', 'LIKE', "%{$search}%");
                });
        }

        // Order the results based on the `created_at` column
        if (!empty($this->filters['order'])) {
            $query->orderBy('created_at', $this->filters['order']);
        }

        return $query;

    }

    public function headings(): array
    {
        return [
            'Number Result',
            'Reference',
            'Start Date',
            'End Date',
            'Customer Name',
            'Bast Number',
            'Tax',
            'Service Fee',
            'Discount',
            'Charges',
            'Total',
            'Status',
            'Created By',
            'Updated By',
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->number_result,
            $invoice->reference,
            $invoice->start_date,
            $invoice->end_date,
            $invoice->customer->name ?? 'N/A',
            $invoice->bast->number_result ?? 'N/A',
            $invoice->tax,
            $invoice->service_fee,
            $invoice->discount,
            $invoice->charges,
            $invoice->total,
            $invoice->status,
            $invoice->createdBy->name ?? 'N/A',
            $invoice->updatedBy->name ?? 'N/A',
        ];
    }

    public function chunkSize(): int
    {
        return 1000; // Number of rows per chunk
    }
}
