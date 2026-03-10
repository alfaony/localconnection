<?php

namespace App\Jobs;

use App\Exports\QuotesExport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
class ExportQuoteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filters;
    protected $filename;
    protected $format;
    protected $company_id;
    protected $division_id;

    /**
     * Create a new job instance.
     */
    public function __construct($filename, $format, $company_id, $division_id = null)
    {
        $this->filename = $filename;
        $this->format = $format;
        $this->company_id = $company_id;
        $this->division_id = $division_id;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $exportFormat = $this->format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        try {
            $checkk = Excel::store(new QuotesExport($this->company_id, $this->division_id), $this->filename, null, $exportFormat);
            Log::info("File successfully stored at: " . s3_asset(true,10,$this->filename));
        } catch (\Exception $e) {
            // dd($e);
            Log::error("Error storing file: " . $e->getMessage());
        }
    }
}
