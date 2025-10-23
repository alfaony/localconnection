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
use Illuminate\Support\Str;

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
        $tempDir = sys_get_temp_dir();
        $tempInputPath = null;
        $cleanPath = null;
        $outputPath = null;

        try {
            $invoice = Invoice::where('slug', $this->invoiceSlug)->firstOrFail();
    
            // Download file dari S3 ke temporary directory
            $tempInputPath = $tempDir . "/input_{$invoice->slug}_" . uniqid() . ".pdf";
            $inputContent = Storage::get($invoice->file_merge_path);
            file_put_contents($tempInputPath, $inputContent);
    
            // Path untuk file temporary
            $cleanPath = $tempDir . "/clean-{$invoice->slug}_" . uniqid() . ".pdf";
            $outputPath = $tempDir . "/pdfa-{$invoice->slug}_" . uniqid() . ".pdf";
    
            // Step 1: Clean PDF
            $cleanProcess = new Process([
                $this->gsPath, '-dBATCH', '-dNOPAUSE', '-sDEVICE=pdfwrite',
                '-dCompatibilityLevel=1.4',
                "-sOutputFile={$cleanPath}",
                $tempInputPath
            ]);
            $cleanProcess->setTimeout(300); // 5 menit timeout
            $cleanProcess->run();
    
            if (!$cleanProcess->isSuccessful()) {
                throw new \Exception("Failed to render PDF: " . $cleanProcess->getErrorOutput());
            }
    
            // Step 2: Convert to PDF/A
            $pdfaProcess = new Process([
                $this->gsPath, '-dPDFA=2', '-dPDFACompatibilityPolicy=1',
                '-dBATCH', '-dNOPAUSE', '-dNOOUTERSAVE',
                '-sDEVICE=pdfwrite',
                "-sOutputFile={$outputPath}",
                $cleanPath
            ]);
            $pdfaProcess->setTimeout(300); // 5 menit timeout
            $pdfaProcess->run();
    
            if (!$pdfaProcess->isSuccessful()) {
                throw new \Exception("Failed to convert to PDF/A: " . $pdfaProcess->getErrorOutput());
            }

            // Upload hasil konversi ke S3
            $s3OutputPath = "invoices/converted/pdfa-{$invoice->slug}.pdf";
            Storage::put($s3OutputPath, file_get_contents($outputPath));

            // Optional: Update invoice record dengan path baru
            // $invoice->update([
            //     'file_pdfa_path' => $s3OutputPath
            // ]);

            Log::info("PDF/A successfully converted and uploaded to S3", [
                'invoice_slug' => $invoice->slug,
                's3_path' => $s3OutputPath,
                'url' => Storage::url($s3OutputPath)
            ]);

        } catch (\Throwable $th) {
            // dd($th);
            Log::error("Error converting PDF to PDF/A", [
                'invoice_slug' => $this->invoiceSlug,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            
            // Re-throw jika ingin job di-retry
            throw $th;
        } finally {
            // Cleanup: Hapus semua temporary files
            $tempFiles = [$tempInputPath, $cleanPath, $outputPath];
            foreach ($tempFiles as $file) {
                if ($file && file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }
}