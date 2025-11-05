<?php

namespace App\Jobs;

use App\Models\InternetPurchaseCoupon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GenerateInternetPurchaseCouponJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public String $internet_customer_id;
    public int $internet_purchase_id;
    public int $jumlah; // jumlah kupon yang ingin dibuat

    // Anti duplikat job selama 2 menit
    public int $uniqueFor = 120;

    /**
     * ID unik job di queue (kombinasi parameter)
     */
    public function uniqueId(): string
    {
        return "{$this->internet_customer_id}-{$this->internet_purchase_id}";
    }

    /**
     * Buat job baru
     */
    public function __construct(String $internet_customer_id, int $internet_purchase_id, int $jumlah = 1)
    {
        $this->internet_customer_id = $internet_customer_id;
        $this->internet_purchase_id = $internet_purchase_id;
        $this->jumlah = max(1, $jumlah); // pastikan minimal 1
    }

    /**
     * Generate kode kupon unik 5 karakter (huruf + angka)
     */
    protected function generateCouponCode(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = substr(str_shuffle(str_repeat($characters, 5)), 0, 5);
        } while (InternetPurchaseCoupon::where('name', $code)->exists());

        return $code;
    }

    /**
     * Jalankan job
     */
    public function handle(): void
    {
        // Anti duplicate: pastikan kombinasi customer & purchase belum pernah di-proses
        $exists = InternetPurchaseCoupon::where([
            'internet_customer_id' => $this->internet_customer_id,
            'internet_purchase_id' => $this->internet_purchase_id,
        ])->exists();

        if ($exists) {
            Log::info("Skip duplicate coupon batch for customer {$this->internet_customer_id}, purchase {$this->internet_purchase_id}");
            return;
        }

        // Buat kupon sesuai jumlah
        for ($i = 0; $i < $this->jumlah; $i++) {
            $code = $this->generateCouponCode();
            // Simpan record kupon
            InternetPurchaseCoupon::create([
                'name' => $code,
                'internet_customer_id' => $this->internet_customer_id,
                'internet_purchase_id' => $this->internet_purchase_id,
            ]);

            Log::info("Created coupon {$code} for customer {$this->internet_customer_id}");
        }
    }
}