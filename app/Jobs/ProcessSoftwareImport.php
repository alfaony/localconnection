<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Software;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Events\SoftwareImportStatusUpdated;

class ProcessSoftwareImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $csvData;
    protected $companyId;
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($csvData, $companyId, $userId)
    {
        $this->csvData = $csvData;
        $this->companyId = $companyId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if (empty($this->csvData)) {
                $this->broadcastError('File kosong atau format tidak valid.');
                return;
            }

            $total = count($this->csvData) - 1; // Exclude header
            if ($total <= 0) {
                $this->broadcastError('Tidak ada baris data untuk diimport.');
                return;
            }

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            // Skip header (index 0)
            foreach ($this->csvData as $index => $row) {
                if ($index === 0) continue;

                // Cek delimiter semicolon jika menggunakan excel dengan region tertentu
                if (count($row) == 1 && strpos($row[0], ';') !== false) {
                    $row = explode(';', $row[0]);
                }

                if (empty(array_filter($row))) continue;

                // Indexing based on Template: Nama Software[0], Tipe Paket[1], Deskripsi[2], Status[3], Email PIC[4]
                $nama = trim($row[0] ?? '');
                $tipePaket = trim($row[1] ?? '');
                $description = trim($row[2] ?? '');
                $status = strtolower(trim($row[3] ?? 'active'));
                $picEmail = trim($row[4] ?? '');

                if (!$nama || !$tipePaket) {
                    $failedCount++;
                    $errors[] = "Baris " . ($index + 2) . ": Nama Software dan Tipe Paket wajib diisi.";
                    continue;
                }

                if (!in_array($status, ['active', 'inactive'])) {
                    $status = 'active';
                }

                // Resolve PIC by email
                $picId = null;
                if ($picEmail) {
                    $pic = User::where('company_id', $this->companyId)->where('email', $picEmail)->first();
                    if ($pic) {
                        $picId = $pic->id;
                    } else {
                        $failedCount++;
                        $errors[] = "Baris " . ($index + 2) . ": Email PIC ($picEmail) tidak ditemukan.";
                        continue;
                    }
                }

                // Generate slug
                $slug = Str::slug($nama . '-' . $tipePaket);
                $count = Software::where('company_id', $this->companyId)->where('slug', $slug)->count();
                if ($count > 0) {
                    $slug .= '-' . Str::random(6);
                }

                // Check duplicate nama + tipe_paket combination
                $existing = Software::where('company_id', $this->companyId)
                    ->where('nama', $nama)
                    ->where('tipe_paket', $tipePaket)
                    ->exists();

                if ($existing) {
                    $failedCount++;
                    $errors[] = "Baris " . ($index + 2) . ": Software '$nama' dengan tipe ($tipePaket) sudah ada.";
                    continue;
                }

                Software::create([
                    'company_id' => $this->companyId,
                    'nama' => $nama,
                    'tipe_paket' => $tipePaket,
                    'slug' => $slug,
                    'description' => $description,
                    'status' => $status,
                    'pic_user_id' => $picId,
                    'logo' => null
                ]);

                $successCount++;

                // Broadcast progress every 5 rows or on last row
                if ($successCount % 5 === 0 || $index === count($this->csvData) - 1) {
                    $progress = round((($index) / $total) * 100);
                    $this->broadcastProgress($progress, "Memproses $successCount dari $total data...");
                }
            }

            $this->broadcastCompleted($successCount, $failedCount, $errors);

        } catch (\Exception $e) {
            Log::error("Software Import Error: " . $e->getMessage());
            $this->broadcastError('Terjadi kesalahan internal saat import.');
        }
    }

    private function broadcastError($message)
    {
        $payload = [
            'status' => 'error',
            'message' => $message,
        ];
        $this->dispatchReverb($payload);
    }

    private function broadcastProgress($percent, $message)
    {
        $payload = [
            'status' => 'progress',
            'percent' => $percent,
            'message' => $message,
        ];
        $this->dispatchReverb($payload);
    }

    private function broadcastCompleted($success, $failed, $errors)
    {
        $payload = [
            'status' => 'completed',
            'success_count' => $success,
            'failed_count' => $failed,
            'errors' => $errors,
            'message' => "Import selesai! Berhasil: $success, Gagal: $failed."
        ];
        
        // Cache to allow check via HTTP if websocket is missed
        Cache::put("latest_software_import_{$this->userId}", $payload, now()->addMinutes(10));
        
        $payload['cache_key'] = "latest_software_import_{$this->userId}";
        $this->dispatchReverb($payload);
    }

    private function dispatchReverb($payload)
    {
        broadcast(new SoftwareImportStatusUpdated($this->userId, $payload));
    }
}
