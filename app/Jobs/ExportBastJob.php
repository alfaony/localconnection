<?php

namespace App\Jobs;

use App\Exports\BastExport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
class ExportBastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filters;
    protected $filename;
    protected $format;
    protected $company_id;

    /**
     * Create a new job instance.
     */
    public function __construct($filename, $format, $company_id)
    {
        $this->filename = $filename;
        $this->format = $format;
        $this->company_id = $company_id;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $exportFormat = $this->format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        
        try {
            Log::info("Starting BAST export: {$this->filename}");
            
            Excel::store(new BastExport($this->company_id), $this->filename, null, $exportFormat);
            
            // Verify file exists after export
            if (Storage::exists($this->filename)) {
                Log::info("BAST export completed successfully: " . s3_asset(true, 10, $this->filename));
            } else {
                Log::error("BAST export failed: File not found after export");
            }
            
        } catch (\Exception $e) {
            Log::error("Error exporting BAST: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Re-throw to trigger job retry
            throw $e;
        }
    }
}

