<?php

namespace App\Jobs;

use App\Models\WebhookSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchWebhooksForAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int|string $companyId;
    public string $appName;
    public array $payload;
    public string $event;
    public ?string $settingId;

    /**
     * Buat job baru.
     */
    public function __construct(int|string $companyId, string $appName, array $payload, string $event, $settingId = null)
    {
        $this->companyId = $companyId;
        $this->appName   = $appName;    // contoh: 'used_laptops'
        $this->payload   = $payload;    // bebas: data yang mau di-push
        $this->event     = $event;      // contoh: 'created' | 'updated' | 'deleted'
        $this->settingId = $settingId ?? null;
    }

    /**
     * Konfigurasi retry/backoff (opsional).
     */
    public $tries = 3;
    public $backoff = [60, 120, 300]; // detik

    /**
     * Eksekusi job.
     */
    public function handle(): void
    {
        $query = WebhookSetting::query();
        
        if($this->settingId) 
        {
            $query->where('id', $this->settingId);
        }else
        {
            $query->byCompany($this->companyId)->hasApp($this->appName);
        }
        
        $settings = $query->get();

        // dd($settings, $this->settingId, $this->companyId, $this->appName);

        if ($settings->isEmpty()) {
            Log::info('Webhook: tidak ada subscriber', [
                'company_id' => $this->companyId,
                'app'        => $this->appName,
            ]);
            return;
        }

        foreach ($settings as $setting) {
            $url = rtrim($setting->url, '/'); // hindari double slash
            // endpoint default: langsung ke base URL; kalau mau spesifik, ubah di sini, mis: $url.'/webhooks'
            $endpoint = $url;

            try {
                $response = Http::retry(3, 200)
                    ->timeout(10)
                    ->acceptJson()
                    ->withToken($setting->token) // header Authorization: Bearer {token}
                    ->asJson()
                    ->post($endpoint, [
                        'app'       => $this->appName,
                        'event'     => $this->event,
                        'data'      => $this->payload,
                        'sent_at'   => now()->toIso8601String(),
                    ]);

                if (! $response->successful()) {
                    Log::warning('Webhook gagal', [
                        'setting_id' => $setting->id,
                        'status'     => $response->status(),
                        'body'       => $response->body(),
                        'endpoint'   => $endpoint,
                    ]);
                } else {
                    Log::info('Webhook sukses', [
                        'setting_id' => $setting->id,
                        'status'     => $response->status(),
                        'endpoint'   => $endpoint,
                    ]);
                }
            } catch (\Throwable $e) {
                // dd($e);
                Log::error('Webhook exception', [
                    'setting_id' => $setting->id,
                    'endpoint'   => $endpoint,
                    'error'      => $e->getMessage(),
                ]);
                // biarin lanjut ke setting berikutnya
            }
        }
    }
}