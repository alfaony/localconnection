<?php

namespace App\Jobs;

use App\Models\BarcodeAttendance;
use App\Models\OfficeAttendance;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\BarcodeVerifiedSuccess;
use App\Events\NewBarcodeGenerated;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use App\Events\AbsensiVerified;

class ProcessScanAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $barcodeId;
    public $userId;
    public $companyId;

    public function __construct($barcodeId, $userId, $companyId)
    {
        $this->barcodeId = $barcodeId;
        $this->userId = $userId;
        $this->companyId = $companyId;
    }

    public function handle()
    {
        try {
            $barcode = BarcodeAttendance::find($this->barcodeId);
            $status = true;
            if ($barcode && !$barcode->is_used) {
                // Lakukan verifikasi absensi
                $barcode->user_id = $this->userId;
                $barcode->is_used = true;
                $barcode->save();
    
                // Trigger event setelah barcode diverifikasi 
                $status = true;

                // Buat QR baru jika belum ada
                $this->generateCode($barcode->company_id, $barcode->user_create_id);
            }
            
            broadcast(new BarcodeVerifiedSuccess($this->userId, $status));
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th->getMessage());
            // dd($th);
        }
    }

    private function generateCode($companyId, $userId) 
    {
         $barcode = BarcodeAttendance::create([
            'id' => Str::uuid(),
            'company_id' => $companyId,
            'user_create_id' => $userId,
            'code' => Str::uuid(),
            'expires_at' => now()->addMinutes(5)
        ]);

        // Broadcast agar QR baru ditampilkan di layar
        broadcast(new NewBarcodeGenerated($barcode, $userId))->toOthers();
    }
}
