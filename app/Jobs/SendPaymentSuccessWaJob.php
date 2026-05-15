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

    protected $purchaseId;

    public function __construct(int $purchaseId)
    {
        $this->purchaseId = $purchaseId;
    }

    public function handle()
    {
        try {
            $purchase = InternetCustomerPurchase::with([
                'customer.userCustomer',
                'customer.internetPackage',
            ])->find($this->purchaseId);

            if (!$purchase) {
                Log::warning("SendPaymentSuccessWaJob: purchase {$this->purchaseId} not found");
                return;
            }

            $internetCustomer = $purchase->customer;
            $userCustomer     = $internetCustomer->userCustomer;

            if (!$userCustomer || !$userCustomer->phone_number) {
                Log::info("SendPaymentSuccessWaJob: no phone number for customer {$internetCustomer->code}");
                return;
            }

            $settingCompany = SettingCompany::byCompany($internetCustomer->company_id)
                ->where('menu', 'wablas')
                ->get()
                ->pluck('field_value', 'field_title');

            if (empty($settingCompany['server_wablas']) || empty($settingCompany['token_wablas'])) {
                Log::warning("SendPaymentSuccessWaJob: Wablas not configured for company {$internetCustomer->company_id}");
                return;
            }

            $internetSettings = SettingCompany::byCompany($internetCustomer->company_id)
                ->where('menu', 'internet_customer_setting')
                ->get()
                ->pluck('field_value', 'field_title');

            $companyName = $internetSettings['internet_company_name'] ?? 'Tim Kami';

            $client = new WablasClient(
                $settingCompany['server_wablas'],
                $settingCompany['token_wablas'],
                $settingCompany['webhook_key_wablas'] ?? null
            );

            if (!$client->status()) {
                Log::warning("SendPaymentSuccessWaJob: Wablas client not active for company {$internetCustomer->company_id}");
                return;
            }

            $packageName   = $internetCustomer->internetPackage->name ?? '-';
            $periodStart   = $purchase->period_start ? Carbon::parse($purchase->period_start)->locale('id')->isoFormat('D MMMM Y') : '-';
            $periodEnd     = $purchase->period_end   ? Carbon::parse($purchase->period_end)->locale('id')->isoFormat('D MMMM Y')   : '-';
            $amountPaid    = $purchase->amount_paid  ? 'Rp ' . number_format($purchase->amount_paid, 0, ',', '.') : '-';
            $paymentMonths = $purchase->payment_months ?? 1;
            $endBilling    = $userCustomer->end_billing_date
                ? Carbon::parse($userCustomer->end_billing_date)->locale('id')->isoFormat('D MMMM Y')
                : '-';

           $message = "*Pembayaran Berhasil Diterima ✅*\n\n"
                . "Yth. Bapak/Ibu *{$userCustomer->name}*,\n\n"
                . "Terima kasih atas pembayaran Anda. Konfirmasi pembayaran langganan internet telah berhasil kami proses. 🎉\n\n"
                . "📋 *Rincian Pembayaran:*\n"
                . "━━━━━━━━━━━━━━━━━━━\n"
                . "ID Pelanggan : *{$internetCustomer->code}*\n"
                . "Paket        : {$packageName}\n"
                . "Periode      : {$periodStart} - {$periodEnd}\n"
                . "Durasi       : {$paymentMonths} bulan\n"
                . "Total Bayar  : *{$amountPaid}*\n"
                . "━━━━━━━━━━━━━━━━━━━\n\n"
                . "📅 *Masa Aktif Hingga: {$endBilling}*\n\n"
                . "Layanan internet Anda akan segera aktif kembali secara otomatis. Apabila terdapat kendala atau pertanyaan lebih lanjut, silakan hubungi pusat bantuan kami di:\n"
                . "🌐 https://support.hikari.net.id\n\n"
                . "Terima kasih telah memercayakan kebutuhan koneksi internet Anda kepada kami. 🙏\n\n"
                . "*Hormat kami,*\n"
                . "*Hikarinet by KAILI Global*";
            
            $send = new Message($client);
            $send->single_text($userCustomer->phone_number, $message);

            Log::info("SendPaymentSuccessWaJob: WA sent to {$userCustomer->phone_number}", [
                'purchase_id'   => $this->purchaseId,
                'customer_code' => $internetCustomer->code,
            ]);
        } catch (\Throwable $th) {
            Log::error("SendPaymentSuccessWaJob failed: " . $th->getMessage(), [
                'purchase_id' => $this->purchaseId,
                'trace'       => $th->getTraceAsString(),
            ]);
        }
    }
}
