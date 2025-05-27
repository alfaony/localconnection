<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use Symfony\Component\Process\Process;

class ConvertInvoiceToPdfAJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoiceSlug, $gsPath;

    public function __construct($invoiceSlug, $gsPath = null)
    {
        $this->invoiceSlug = $invoiceSlug;
        $this->gsPath = $gsPath ?? 'gs';
    }

    public function handle()
    {
        try {
            $invoice = Invoice::where('slug', $this->invoiceSlug)->firstOrFail();
    
            $inputPath = Storage::path($invoice->file_merge_path);
            $cleanPath = storage_path("app/public/invoices/converted/clean-{$invoice->slug}.pdf");
            $outputPath = storage_path("app/public/invoices/converted/pdfa-{$invoice->slug}.pdf");
    
            if (!is_dir(dirname($cleanPath))) {
                mkdir(dirname($cleanPath), 0755, true);
            }
    
            $cleanProcess = new Process([
                $this->gsPath, '-dBATCH', '-dNOPAUSE', '-sDEVICE=pdfwrite',
                '-dCompatibilityLevel=1.4',
                "-sOutputFile={$cleanPath}",
                $inputPath
            ]);
            $cleanProcess->run();
    
            if (!$cleanProcess->isSuccessful()) {
                throw new \Exception("Failed to render PDF: " . $cleanProcess->getErrorOutput());
            }
    
            $pdfaProcess = new Process([
                $this->gsPath, '-dPDFA=2', '-dPDFACompatibilityPolicy=1',
                '-dBATCH', '-dNOPAUSE', '-dNOOUTERSAVE',
                '-sDEVICE=pdfwrite',
                "-sOutputFile={$outputPath}",
                $cleanPath
            ]);
            $pdfaProcess->run();
    
            if (!$pdfaProcess->isSuccessful()) {
                throw new \Exception("Failed to convert to PDF/A: " . $pdfaProcess->getErrorOutput());
            }

            Log::info("File successfully stored at: " . Storage::url($outputPath));
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error("Error storing file: " . $th->getMessage());
        }
    }
}
