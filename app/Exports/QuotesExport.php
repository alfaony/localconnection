<?php

namespace App\Exports;

use Illuminate\Support\Facades\Auth;

use App\Models\Quote;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class QuotesExport implements FromQuery, WithMapping, WithHeadings, WithChunkReading
{
    use Exportable;
    protected $company_id;

    public function __construct($company_id)
    {
        $this->company_id = $company_id;
    }
    public function query()
    {
        return Quote::query()->byCompany($this->company_id)->with('quoteProduct'); // Load related models if necessary
    }

    public function headings(): array
    {
        return [
            'Quote Number', 'Total', 'Customer', 'budget_transition', 'Status','User',
            // Add more columns as needed
        ];
    }   

    public function map($quote): array
    {
        return [
            $quote->number_result,
            'Rp. '.number_format($quote->total,0,',','.'),
            $quote->customer->name,
            $quote->budget_transition ? 'Yes' : '',
            $quote->status,
            $quote->userCreate->name
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
