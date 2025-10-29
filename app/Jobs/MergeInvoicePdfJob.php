<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\XeroBos;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MergeInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $invoiceId;
    public $bastFilePath;
    public $companyId; // ← TAMBAH INI
    
    public $tries = 3;
    public $maxExceptions = 3;
    public $timeout = 300;
    public $backoff = [60, 300, 600];

    // ← UPDATE CONSTRUCTOR
    public function __construct($invoiceId, $bastFilePath, $companyId = null)
    {
        $this->invoiceId = $invoiceId;
        $this->bastFilePath = $bastFilePath;
        $this->companyId = $companyId; // ← SIMPAN COMPANY ID
    }

    public function handle()
    {
        $tempInvoicePdfPath = null;
        $tempBastPath = null;
        $tempMergedPath = null;

        try {
            $invoice = Invoice::findOrFail($this->invoiceId);
            
            // ← FIX: Set company ID SEBELUM inisialisasi XeroBos
            $companyId = $this->companyId ?? $invoice->company_id;
            
            if (!$companyId) {
                throw new \Exception("Company ID tidak ditemukan untuk invoice {$invoice->id}");
            }
            
            // Inisialisasi XeroBos dengan company ID
            $xero = new XeroBos();
            $xero->setCompanyPublic($companyId); // ← PENTING: Set sebelum method apapun
            
            Log::info("Starting merge PDF process", [
                'invoice_id' => $invoice->id,
                'company_id' => $companyId,
                'xero_invoice_id' => $invoice->invoice_xero_id
            ]);
            
            // PRE-FLIGHT CHECKS
            if (!$invoice->invoice_xero_id) {
                Log::warning("Invoice tidak memiliki Xero ID", [
                    'invoice_id' => $invoice->id
                ]);
                return;
            }

            if (!Storage::exists($this->bastFilePath)) {
                Log::warning("File BAST tidak ditemukan", [
                    'invoice_id' => $invoice->id,
                    'bast_path' => $this->bastFilePath
                ]);
                return;
            }

            // CEK XERO CONNECTION
            if (!$this->checkXeroConnection($xero)) {
                Log::warning("Xero tidak tersedia, job akan di-retry", [
                    'invoice_id' => $invoice->id,
                    'attempt' => $this->attempts()
                ]);
                $this->release(300);
                return;
            }

            $outputPath = "invoices/merged_invoice_{$invoice->number_result}_".date('YmdHis').'_'.Str::random(5).".pdf";
            
            if ($invoice->file_merge_path && Storage::exists($invoice->file_merge_path)) {
                Storage::delete($invoice->file_merge_path);
            }
            
            // DOWNLOAD PDF DARI XERO
            $tempInvoicePdfPath = sys_get_temp_dir() . "/invoice_temp_{$invoice->id}_" . uniqid() . ".pdf";
            
            try {
                Log::info("Downloading PDF from Xero", [
                    'invoice_id' => $invoice->id,
                    'xero_invoice_id' => $invoice->invoice_xero_id
                ]);
                
                $response = $xero->get(
                    "Invoices/{$invoice->invoice_xero_id}",
                    [],
                    true,
                    'application/pdf'
                );
                
                $xeroInvoicePdf = $response['body'];
                
                Log::debug("Xero PDF response", [
                    'invoice_id' => $invoice->id,
                    'response_size' => strlen($xeroInvoicePdf),
                    'content_type' => $response['headers']['Content-Type'][0] ?? 'unknown',
                    'first_20_bytes_hex' => bin2hex(substr($xeroInvoicePdf, 0, 20))
                ]);
                
            } catch (\Exception $e) {
                Log::error("Gagal download invoice dari Xero", [
                    'invoice_id' => $invoice->id,
                    'xero_id' => $invoice->invoice_xero_id,
                    'error' => $e->getMessage()
                ]);
                
                if ($this->isTokenError($e)) {
                    Log::info("Token error detected, akan retry", [
                        'invoice_id' => $invoice->id
                    ]);
                    $this->release(60);
                    return;
                }
                
                throw $e;
            }
            
            if (empty($xeroInvoicePdf)) {
                throw new \Exception("PDF dari Xero kosong");
            }
            
            $pdfContent = $this->extractPdfFromResponse($xeroInvoicePdf);
            
            if (empty($pdfContent)) {
                throw new \Exception("Tidak dapat mengekstrak PDF dari response Xero");
            }
            
            file_put_contents($tempInvoicePdfPath, $pdfContent);
            
            if (!file_exists($tempInvoicePdfPath) || filesize($tempInvoicePdfPath) === 0) {
                throw new \Exception("Gagal menyimpan PDF dari Xero ke temporary file");
            }
            
            $fileHeader = file_get_contents($tempInvoicePdfPath, false, null, 0, 10);
            if (strpos($fileHeader, '%PDF') === false) {
                $debugPath = storage_path("logs/debug_xero_pdf_{$invoice->id}_" . date('YmdHis') . ".bin");
                file_put_contents($debugPath, substr($pdfContent, 0, 2000));
                
                throw new \Exception(
                    "File dari Xero bukan PDF yang valid. " .
                    "Header: " . bin2hex($fileHeader) . " " .
                    "Debug file saved: {$debugPath}"
                );
            }
            
            Log::info("PDF dari Xero valid", [
                'invoice_id' => $invoice->id,
                'file_size' => filesize($tempInvoicePdfPath)
            ]);
            
            // INISIALISASI FPDI
            $pdf = new \setasign\Fpdi\Fpdi();
            
            // TAMBAHKAN HALAMAN DARI INVOICE XERO
            $pageCount = $pdf->setSourceFile($tempInvoicePdfPath);
            Log::info("Adding Xero invoice pages", [
                'invoice_id' => $invoice->id,
                'page_count' => $pageCount
            ]);
            
            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }

            // DOWNLOAD FILE BAST DARI S3
            $tempBastPath = sys_get_temp_dir() . '/temp_bast_' . uniqid() . '.pdf';
            
            Log::info("Downloading BAST from S3", [
                'invoice_id' => $invoice->id,
                'bast_path' => $this->bastFilePath
            ]);
            
            $bastContent = Storage::get($this->bastFilePath);
            
            if (empty($bastContent)) {
                throw new \Exception("File BAST kosong");
            }
            
            file_put_contents($tempBastPath, $bastContent);
            
            if (!file_exists($tempBastPath) || filesize($tempBastPath) === 0) {
                throw new \Exception("Gagal menyimpan file BAST ke temporary file");
            }

            // TAMBAHKAN HALAMAN DARI BAST
            $pageCount = $pdf->setSourceFile($tempBastPath);
            Log::info("Adding BAST pages", [
                'invoice_id' => $invoice->id,
                'page_count' => $pageCount
            ]);
            
            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }

            // SIMPAN HASIL GABUNGAN
            $tempMergedPath = sys_get_temp_dir() . '/merged_' . uniqid() . '.pdf';
            $pdf->Output($tempMergedPath, 'F');
            
            if (!file_exists($tempMergedPath) || filesize($tempMergedPath) === 0) {
                throw new \Exception("Gagal membuat file PDF gabungan");
            }

            Log::info("Merged PDF created", [
                'invoice_id' => $invoice->id,
                'file_size' => filesize($tempMergedPath)
            ]);

            // UPLOAD KE S3
            $uploadResult = Storage::put($outputPath, file_get_contents($tempMergedPath));
            
            if (!$uploadResult) {
                throw new \Exception("Gagal upload file gabungan ke S3");
            }

            // UPDATE INVOICE
            $invoice->file_merge_path = $outputPath;
            $invoice->save();

            Log::info("PDF merge berhasil", [
                'invoice_id' => $invoice->id,
                'output_path' => $outputPath,
                'file_size' => filesize($tempMergedPath),
                'attempts' => $this->attempts()
            ]);

        } catch (\Throwable $e) {
            Log::error("Error saat merge PDF", [
                'invoice_id' => $this->invoiceId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'attempts' => $this->attempts()
            ]);
            
            if ($this->attempts() >= $this->tries) {
                Log::critical("Max attempts reached for merge PDF", [
                    'invoice_id' => $this->invoiceId,
                    'error' => $e->getMessage()
                ]);
                return;
            }
            
            throw $e;
            
        } finally {
            $tempFiles = [$tempInvoicePdfPath, $tempBastPath, $tempMergedPath];
            foreach ($tempFiles as $file) {
                if ($file && file_exists($file)) {
                    @unlink($file);
                }
            }
        }
    }

    private function checkXeroConnection(XeroBos $xero): bool
    {
        try {
            if (!$xero->isConnected()) {
                Log::warning("Xero belum connected untuk company ini");
                return false;
            }
            
            $accessToken = $xero->getAccessToken(false);
            
            if (empty($accessToken)) {
                Log::warning("Access token kosong");
                return false;
            }
            
            $response = $xero->get('Organisation');
            
            Log::info("Xero connection check success");
            
            return true;
            
        } catch (\Exception $e) {
            Log::warning("Xero connection check failed", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function isTokenError(\Exception $e): bool
    {
        $message = strtolower($e->getMessage());
        return str_contains($message, 'token') 
            || str_contains($message, 'unauthorized') 
            || str_contains($message, '401')
            || str_contains($message, 'authentication')
            || str_contains($message, 'expired')
            || str_contains($message, 'credentials');
    }

    private function extractPdfFromResponse($response)
    {
        if (is_string($response) && strpos($response, "HTTP/") === 0) {
            $parts = explode("\r\n\r\n", $response, 2);
            if (count($parts) === 2) {
                return $parts[1];
            }
            
            $parts = explode("\n\n", $response, 2);
            if (count($parts) === 2) {
                return $parts[1];
            }
        }
        
        if (is_string($response) && strpos($response, '%PDF') === 0) {
            return $response;
        }
        
        if (is_string($response)) {
            $pdfStart = strpos($response, '%PDF');
            if ($pdfStart !== false) {
                return substr($response, $pdfStart);
            }
        }
        
        return $response;
    }

    public function failed(\Throwable $exception)
    {
        Log::critical("Merge PDF job failed permanently", [
            'invoice_id' => $this->invoiceId,
            'bast_file_path' => $this->bastFilePath,
            'error' => $exception->getMessage()
        ]);
    }
}