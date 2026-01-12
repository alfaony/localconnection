<?php

namespace App\Exports;

use App\Models\Bast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class BastExport implements 
    FromQuery, 
    WithHeadings, 
    WithMapping, 
    WithChunkReading, 
    WithCustomCsvSettings,
    WithStrictNullComparison,
    ShouldQueue
{
    protected $company_id;

    public function __construct($company_id)
    {
        $this->company_id = $company_id;
    }
    
    public function query()
    {
        return Bast::byCompany($this->company_id)->with('workOrder')->orderBy('date', 'desc');
    }

    public function headings(): array
    {
        return ['Date', 'BAST Number', 'Work Order Number'];
    }

    public function map($bast): array
    {
        try {
            $date = $bast->date ? \Carbon\Carbon::parse($bast->date)->format('d-m-Y') : '-';
            $bastNumber = $bast->number_result ?? '-';
            $workOrderNumber = optional($bast->workOrder)->number_result ?? '-';
            
            return [
                $date,
                $bastNumber,
                $workOrderNumber,
            ];
        } catch (\Exception $e) {
            \Log::error('Error mapping BAST data: ' . $e->getMessage());
            return ['-', '-', '-'];
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
    
    /**
     * Configure CSV settings for proper encoding
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => "\r\n",
            'use_bom' => true, // Add UTF-8 BOM for Excel compatibility
            'include_separator_line' => false,
            'excel_compatibility' => true,
        ];
    }
}

