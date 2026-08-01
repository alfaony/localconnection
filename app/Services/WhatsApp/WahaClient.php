<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client untuk WAHA (WhatsApp HTTP API) — https://waha.devlike.pro
 * Self-hosted, jalan sebagai container Docker sendiri, komunikasi via
 * REST API biasa. Tidak ada biaya per-pesan seperti provider SaaS.
 *
 * Prasyarat sebelum ini bisa dipakai:
 * 1. WAHA container udah jalan (docker run devlikeapro/waha, atau
 *    docker-compose) dan bisa diakses dari server Laravel
 * 2. Session WhatsApp udah di-scan QR code dan aktif (dilakukan lewat
 *    WAHA dashboard/API sendiri, di luar aplikasi Laravel ini — nomor WA
 *    yang dipakai buat kirim pesan)
 *
 * WAHA base URL dan session name disimpan per company (SettingCompany),
 * sama pola dengan Wablas sebelumnya.
 */
class WahaClient implements WhatsAppSender
{
    public function __construct(
        protected string $baseUrl,
        protected string $session = 'default',
        protected ?string $apiKey = null,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function sendText(string $phone, string $message): array
    {
        $chatId = $this->toChatId($phone);

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->post("{$this->baseUrl}/api/sendText", [
                    'session' => $this->session,
                    'chatId'  => $chatId,
                    'text'    => $message,
                ]);

            if ($response->failed()) {
                throw new WhatsAppSendException(
                    "WAHA gagal kirim pesan (HTTP {$response->status()}): {$response->body()}"
                );
            }

            return $response->json() ?? [];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[WahaClient] Tidak bisa konek ke WAHA server', [
                'base_url' => $this->baseUrl,
                'error' => $e->getMessage(),
            ]);
            throw new WhatsAppSendException("Tidak bisa konek ke WAHA server ({$this->baseUrl}): {$e->getMessage()}");
        }
    }

    /**
     * Cek status session WAHA (aktif/butuh scan QR/dll). Berguna buat
     * health check sebelum kirim pesan penting, atau buat ditampilkan
     * di halaman admin.
     */
    public function sessionStatus(): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(10)
            ->get("{$this->baseUrl}/api/sessions/{$this->session}");

        if ($response->failed()) {
            throw new WhatsAppSendException(
                "Gagal cek status session WAHA (HTTP {$response->status()}): {$response->body()}"
            );
        }

        return $response->json() ?? [];
    }

    protected function headers(): array
    {
        $headers = ['Content-Type' => 'application/json'];

        if ($this->apiKey) {
            $headers['X-Api-Key'] = $this->apiKey;
        }

        return $headers;
    }

    /**
     * WAHA butuh format chatId ala WhatsApp Web JS: "<nomor internasional
     * tanpa +>@c.us" — misal "628123456789@c.us". Normalisasi dari
     * berbagai format input umum (08xxx, +62xxx, 62xxx).
     */
    protected function toChatId(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (!str_starts_with($digits, '62')) {
            $digits = '62' . $digits;
        }

        return "{$digits}@c.us";
    }
}
