<?php

namespace App\Services\PlatformBilling;

use App\Models\PlatformInvoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Midtrans client buat tagihan PLATFORM ke Company (bukan Company ke
 * end-customer mereka). Beda dari App\Services\MidtransService yang kredensialnya
 * per-company (SettingCompany) — di sini kredensialnya SATU akun milik platform,
 * dari config/env.
 */
class PlatformBillingMidtransService
{
    protected ?string $serverKey;
    protected ?string $clientKey;
    protected bool $isProduction;

    public function __construct()
    {
        $this->serverKey = config('services.platform_billing.midtrans.server_key');
        $this->clientKey = config('services.platform_billing.midtrans.client_key');
        $this->isProduction = (bool) config('services.platform_billing.midtrans.is_production', false);
    }

    public function isActive(): bool
    {
        return !empty($this->serverKey) && !empty($this->clientKey);
    }

    protected function getApiUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    public function getRedirectUrl(string $snapToken): string
    {
        return $this->isProduction
            ? "https://app.midtrans.com/snap/v2/vtweb/{$snapToken}"
            : "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}";
    }

    /**
     * Buat SNAP transaction untuk satu PlatformInvoice. order_id memakai suffix
     * "_platformBilling" supaya webhook midtrans/webhook bisa disambiguasi dari
     * transaksi InternetCustomerPurchase/software-sharing yang lewat endpoint sama.
     */
    public function createSnapTransaction(PlatformInvoice $invoice): array
    {
        if (!$this->isActive()) {
            return ['success' => false, 'message' => 'Platform Midtrans belum dikonfigurasi'];
        }

        $orderId = $invoice->id . '_platformBilling';

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $invoice->amount,
            ],
            'item_details' => [[
                'id' => 'PLATFORM-BILLING-' . $invoice->period,
                'price' => (int) $invoice->amount,
                'quantity' => 1,
                'name' => substr("Tagihan Platform {$invoice->period} ({$invoice->billed_customer_count} customer)", 0, 50),
            ]],
            'customer_details' => [
                'first_name' => $invoice->company->name ?? 'Company',
            ],
            'enabled_payments' => [
                'credit_card', 'bca_va', 'bni_va', 'bri_va', 'mandiri_va', 'permata_va', 'other_va', 'gopay', 'shopeepay', 'qris',
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':'),
            ])->timeout(30)->post($this->getApiUrl(), $payload);

            if ($response->failed()) {
                $errorBody = $response->json();
                Log::error('Platform billing Midtrans SNAP transaction failed', [
                    'invoice_id' => $invoice->id,
                    'status' => $response->status(),
                    'body' => $errorBody,
                ]);

                return [
                    'success' => false,
                    'message' => $errorBody['error_messages'][0] ?? 'Gagal membuat transaksi Midtrans',
                ];
            }

            $result = $response->json();
            $snapToken = $result['token'] ?? null;

            if (!$snapToken) {
                return ['success' => false, 'message' => 'Snap token tidak ditemukan di response Midtrans'];
            }

            return [
                'success' => true,
                'order_id' => $orderId,
                'snap_token' => $snapToken,
                'redirect_url' => $this->getRedirectUrl($snapToken),
            ];
        } catch (\Exception $e) {
            Log::error('Platform billing Midtrans SNAP transaction exception', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verifikasi signature notifikasi webhook. Sama persis algoritmanya dengan
     * App\Services\MidtransService::verifyNotification(), cuma serverKey-nya
     * milik platform.
     */
    public function verifyNotification(array $data): bool
    {
        $orderId = $data['order_id'] ?? null;
        $statusCode = $data['status_code'] ?? null;
        $grossAmount = $data['gross_amount'] ?? null;
        $signatureKey = $data['signature_key'] ?? null;

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey || !$this->serverKey) {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }
}
