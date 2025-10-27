<?php

namespace App\Jobs;

use App\Models\Bast;
use App\Models\SettingCompany;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\InboxHelper;

class MergeBastPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bastId;
    public $reportProjectId;
    public $companyId;
    public $userTo;
    public $userFrom;
    
    public $tries = 3;
    public $maxExceptions = 3;
    public $timeout = 300;
    public $backoff = [60, 300, 600];

    /**
     * Create a new job instance.
     *
     * @param int $bastId
     * @param int $reportProjectId (optional) - ID report project untuk mengambil reportedDetails
     * @param int $companyId
     */
    public function __construct($bastId, $reportProjectId = null, $companyId = null, $userTo, $userFrom)
    {
        $this->bastId = $bastId;
        $this->reportProjectId = $reportProjectId;
        $this->companyId = $companyId;
        $this->userTo = $userTo;
        $this->userFrom = $userFrom;
    }

    public function handle()
    {
        $tempFiles = [];
        $tempMergedPath = null;
        $localTemp = null;

        try {
            $bast = Bast::findOrFail($this->bastId);
            
            // Set company ID
            $companyId = $this->companyId ?? $bast->company_id;
            
            if (!$companyId) {
                throw new \Exception("Company ID tidak ditemukan untuk BAST {$bast->id}");
            }
            
            Log::info("Starting merge BAST PDF process", [
                'bast_id' => $bast->id,
                'company_id' => $companyId,
                'report_project_id' => $this->reportProjectId
            ]);
            
            // PRE-FLIGHT CHECKS
            if (!$bast->template) {
                Log::warning("BAST tidak memiliki template", [
                    'bast_id' => $bast->id
                ]);
                return;
            }

            // Setup local temporary folder
            $localTemp = storage_path('app/temp_merge_' . uniqid());
            if (!file_exists($localTemp)) {
                mkdir($localTemp, 0755, true);
            }

            // Hapus file merge lama jika ada
            if ($bast->file_merge_path && Storage::exists($bast->file_merge_path)) {
                Storage::delete($bast->file_merge_path);
                Log::info("Deleted old merged file", [
                    'bast_id' => $bast->id,
                    'old_path' => $bast->file_merge_path
                ]);
            }

            // KUMPULKAN FILE PDF
            $pdfFiles = [];

            // 1. GENERATE PDF DARI VIEW TEMPLATE (COVER/UTAMA)
            Log::info("Generating PDF from template view", [
                'bast_id' => $bast->id,
                'template' => $bast->template
            ]);

            $today = Carbon::now()->format('d M Y');
            $company = SettingCompany::byCompany($companyId)
                ->get()
                ->pluck('field_value', 'field_title');

            $templatePdf = Pdf::loadView('bast.' . $bast->template, compact('bast', 'today', 'company'));
            $templateLocalPath = $localTemp . '/template_bast_' . $bast->id . '.pdf';
            file_put_contents($templateLocalPath, $templatePdf->output());
            
            if (!file_exists($templateLocalPath) || filesize($templateLocalPath) === 0) {
                throw new \Exception("Gagal generate PDF dari template view");
            }
            
            $pdfFiles[] = $templateLocalPath;
            $tempFiles[] = $templateLocalPath;
            
            Log::info("Template PDF generated", [
                'bast_id' => $bast->id,
                'file_size' => filesize($templateLocalPath)
            ]);

            // 2. TAMBAHKAN PDF DARI REPORTED DETAILS (jika ada)
            if ($this->reportProjectId) {
                $reportProject = \App\Models\ReportProject::find($this->reportProjectId);
                
                if ($reportProject && $reportProject->reportedDetails) {
                    Log::info("Processing reported details", [
                        'bast_id' => $bast->id,
                        'details_count' => $reportProject->reportedDetails->count()
                    ]);

                    foreach ($reportProject->reportedDetails as $index => $detail) {
                        if (!$detail->file) {
                            Log::debug("Detail has no file, skip", [
                                'bast_id' => $bast->id,
                                'detail_id' => $detail->id
                            ]);
                            continue;
                        }

                        $remotePath = 'reports/' . $detail->file;

                        if (!Storage::exists($remotePath)) {
                            Log::warning("Detail file not found in S3, skip", [
                                'bast_id' => $bast->id,
                                'detail_id' => $detail->id,
                                'remote_path' => $remotePath
                            ]);
                            continue;
                        }

                        $localPath = $localTemp . '/detail_' . $index . '_' . basename($remotePath);
                        
                        try {
                            $content = Storage::get($remotePath);
                            
                            if (empty($content)) {
                                Log::warning("Detail file is empty, skip", [
                                    'bast_id' => $bast->id,
                                    'detail_id' => $detail->id
                                ]);
                                continue;
                            }

                            file_put_contents($localPath, $content);
                            
                            if (!file_exists($localPath) || filesize($localPath) === 0) {
                                Log::warning("Failed to save detail file locally, skip", [
                                    'bast_id' => $bast->id,
                                    'detail_id' => $detail->id
                                ]);
                                continue;
                            }

                            // Validasi PDF header
                            $fileHeader = file_get_contents($localPath, false, null, 0, 10);
                            if (strpos($fileHeader, '%PDF') === false) {
                                Log::warning("Detail file is not valid PDF, skip", [
                                    'bast_id' => $bast->id,
                                    'detail_id' => $detail->id,
                                    'file' => $detail->file
                                ]);
                                @unlink($localPath);
                                continue;
                            }

                            $pdfFiles[] = $localPath;
                            $tempFiles[] = $localPath;
                            
                            Log::info("Detail file added", [
                                'bast_id' => $bast->id,
                                'detail_id' => $detail->id,
                                'file_size' => filesize($localPath)
                            ]);

                        } catch (\Exception $e) {
                            Log::error("Error processing detail file", [
                                'bast_id' => $bast->id,
                                'detail_id' => $detail->id,
                                'error' => $e->getMessage()
                            ]);
                            continue;
                        }
                    }
                }
            }

            if (empty($pdfFiles)) {
                throw new \Exception("Tidak ada file PDF untuk di-merge");
            }

            Log::info("Total PDF files to merge", [
                'bast_id' => $bast->id,
                'total_files' => count($pdfFiles)
            ]);

            // INISIALISASI FPDI DAN MERGE SEMUA PDF
            $mergedPdf = new \setasign\Fpdi\Fpdi();

            foreach ($pdfFiles as $fileIndex => $pdfFile) {
                try {
                    Log::debug("Processing PDF file for merge", [
                        'bast_id' => $bast->id,
                        'file_index' => $fileIndex,
                        'file_path' => basename($pdfFile)
                    ]);

                    $pageCount = $mergedPdf->setSourceFile($pdfFile);
                    
                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $templateId = $mergedPdf->importPage($pageNo);
                        $size = $mergedPdf->getTemplateSize($templateId);
                        $mergedPdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $mergedPdf->useTemplate($templateId);
                    }

                    Log::info("PDF file merged successfully", [
                        'bast_id' => $bast->id,
                        'file_index' => $fileIndex,
                        'pages_added' => $pageCount
                    ]);

                } catch (\Exception $e) {
                    Log::error("Failed to merge PDF file, skip", [
                        'bast_id' => $bast->id,
                        'file_index' => $fileIndex,
                        'file' => basename($pdfFile),
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            // SIMPAN HASIL MERGE KE LOCAL TEMPORARY
            $finalFileName = 'merged_' . str_replace('/', '_', $bast->number_result) . '_' . date('YmdHis') . '_' . Str::random(5) . '.pdf';
            $tempMergedPath = $localTemp . '/' . $finalFileName;
            $mergedPdf->Output($tempMergedPath, 'F');

            if (!file_exists($tempMergedPath) || filesize($tempMergedPath) === 0) {
                throw new \Exception("Gagal membuat file PDF gabungan");
            }

            Log::info("Merged PDF created", [
                'bast_id' => $bast->id,
                'file_size' => filesize($tempMergedPath)
            ]);

            // UPLOAD KE S3
            $remoteFinalPath = 'reports/' . $finalFileName;
            $uploadResult = Storage::put($remoteFinalPath, file_get_contents($tempMergedPath));

            if (!$uploadResult) {
                throw new \Exception("Gagal upload file gabungan ke S3");
            }

            Log::info("Merged PDF uploaded to S3", [
                'bast_id' => $bast->id,
                'remote_path' => $remoteFinalPath
            ]);

            // UPDATE BAST
            $bast->file_merge_path = $remoteFinalPath;
            $bast->save();

            Log::info("BAST PDF merge berhasil", [
                'bast_id' => $bast->id,
                'output_path' => $remoteFinalPath,
                'file_size' => filesize($tempMergedPath),
                'total_files_merged' => count($pdfFiles),
                'attempts' => $this->attempts()
            ]);

            $inbox = new InboxHelper();
            $inbox->sent($this->userTo, $this->userFrom, "Bast ".$bast->number_result. " PDF Merge Berhasil",route('bast.show', $bast->slug), true);

        } catch (\Throwable $e) {
            // dd($e);
            Log::error("Error saat merge BAST PDF", [
                'bast_id' => $this->bastId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'attempts' => $this->attempts()
            ]);

            if ($this->attempts() >= $this->tries) {
                Log::critical("Max attempts reached for merge BAST PDF", [
                    'bast_id' => $this->bastId,
                    'error' => $e->getMessage()
                ]);
                return;
            }

            throw $e;

        } finally {
            // Cleanup semua temporary files
            if ($tempMergedPath && file_exists($tempMergedPath)) {
                @unlink($tempMergedPath);
            }

            foreach ($tempFiles as $file) {
                if ($file && file_exists($file)) {
                    @unlink($file);
                }
            }

            // Hapus folder temporary
            if ($localTemp && file_exists($localTemp)) {
                @rmdir($localTemp);
            }

            Log::debug("Cleanup completed", [
                'bast_id' => $this->bastId
            ]);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::critical("Merge BAST PDF job failed permanently", [
            'bast_id' => $this->bastId,
            'report_project_id' => $this->reportProjectId,
            'company_id' => $this->companyId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}