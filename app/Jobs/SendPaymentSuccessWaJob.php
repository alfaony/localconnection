<?php

namespace App\Jobs;

use App\Models\InternetCustomerPurchase;
use App\Models\SettingCompany;
use App\Services\Weblas\Message;
use App\Services\Weblas\WablasClient;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPaymentSuccessWaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $purchaseId) {}

    public function handle(): void
    {
        try {
            $purchase = InternetCustomerPurchase::with([
                'customer.userCustomer',
                'customer.internetPackage',
            ])->findOrFail($this->purchaseId);

            $internetCustomer = $purchase->customer;
            $userCustomer     = $internetCustomer->userCustomer;

            if (!$userCustomer || !$userCustomer->phone_number) {
                Log::info('[SendPaymentSuccessWaJob] No phone number, skip', ['purchase_id' => $this->purchaseId]);
                return;
            }

            $companyId = $internetCustomer->company_id;

            // Ambil Wablas credentials
            $wablasSetting = SettingCompany::byCompany($companyId)
                ->where('menu', 'wablas')
                ->get()
                ->pluck('field_value', 'field_title');

            if (empty($wablasSetting['server_wablas']) || empty($wablasSetting['token_wablas'])) {
                Log::warning('[SendPaymentSuccessWaJob] Wablas not configured', ['company_id' => $companyId]);
                return;
            }

            // Ambil template pesan dari setting
            $internetSetting = SettingCompany::byCompany($companyId)
                ->where('menu', 'internet_customer_setting')
                ->get()
                ->pluck('field_value', 'field_title');

            $template = trim($internetSetting['internet_message_success'] ?? '');

            if (empty($template)) {
                Log::info('[SendPaymentSuccessWaJob] Template kosong, skip kirim WA', ['purchase_id' => $this->purchaseId]);
                return;
            }

            $client = new WablasClient(
                $wablasSetting['server_wablas'],
                $wablasSetting['token_wablas'],
                $wablasSetting['webhook_key_wablas'] ?? null
            );

            if (!$client->status()) {
                Log::error('[SendPaymentSuccessWaJob] Wablas client status check failed');
                return;
            }

            $url = route('internet-customer.customer.show', $internetCustomer->code);

            $message = strtr($template, [
                '{{nama}}'    => $userCustomer->name,
                '{{kode}}'    => $internetCustomer->code,
                '{{paket}}'   => $internetCustomer->internetPackage?->name ?? '-',
                '{{tagihan}}' => 'Rp. ' . number_format($purchase->amount_paid ?? 0, 2, ',', '.'),
                '{{url}}'     => $url,
            ]);

            $send     = new Message($client);
            $response = $send->single_text($userCustomer->phone_number, $message);

            \App\Models\WablasLog::record(
                source: 'internet_customer',
                sourceId: $internetCustomer->id,
                phone: $userCustomer->phone_number,
                message: $message,
                response: $response ?? [],
                type: 'text'
            );

            Log::info('[SendPaymentSuccessWaJob] Pesan sukses pembayaran terkirim', [
                'customer_code' => $internetCustomer->code,
                'phone'         => $userCustomer->phone_number,
            ]);
        } catch (\Throwable $th) {
            Log::error('[SendPaymentSuccessWaJob] Error: ' . $th->getMessage(), [
                'purchase_id' => $this->purchaseId,
            ]);
        }
    }

    public function tags(): array
    {
        return ['payment-success-wa', 'purchase:' . $this->purchaseId];
    }
}
