<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\InternetCustomerPurchase;
use App\Models\SettingCompany;
use App\Models\WablasLog;
use App\Services\Weblas\Message;
use App\Services\Weblas\WablasClient;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

class SendNewCustomerRegistrationWaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $internetCustomerId,
        public ?string $purchaseId = null,
    ) {}

    public function handle(): void
    {
        try {
            $customer = InternetCustomer::with([
                'userCustomer',
                'internetPackage',
                'province',
                'city',
                'district',
                'subdistrict',
                'promo',
            ])->findOrFail($this->internetCustomerId);

            $companyId = $customer->company_id;

            $settings = SettingCompany::byCompany($companyId)->get()->pluck('field_value', 'field_title');

            $officePhone = $settings['internet_phone'] ?? null;
            if (empty($officePhone)) {
                Log::info('[SendNewCustomerRegistrationWaJob] internet_phone kosong, skip', ['customer_id' => $this->internetCustomerId]);
                return;
            }

            $serverWablas  = $settings['server_wablas']  ?? null;
            $tokenWablas   = $settings['token_wablas']   ?? null;
            $webhookKey    = $settings['webhook_key_wablas'] ?? null;

            if (empty($serverWablas) || empty($tokenWablas)) {
                Log::warning('[SendNewCustomerRegistrationWaJob] Wablas tidak dikonfigurasi', ['company_id' => $companyId]);
                return;
            }

            $client = new WablasClient($serverWablas, $tokenWablas, $webhookKey);

            if (!$client->status()) {
                Log::error('[SendNewCustomerRegistrationWaJob] Wablas client tidak terhubung');
                return;
            }

            $message = $this->buildMessage($customer, $settings);

            $send     = new Message($client);
            $response = $send->single_text($officePhone, $message);

            WablasLog::record(
                source: 'internet_customer_registration',
                sourceId: $customer->id,
                phone: $officePhone,
                message: $message,
                response: $response ?? [],
                type: 'text'
            );

            Log::info('[SendNewCustomerRegistrationWaJob] Notifikasi terkirim ke kantor', [
                'customer_code' => $customer->code,
                'office_phone'  => $officePhone,
            ]);

        } catch (\Throwable $th) {
            Log::error('[SendNewCustomerRegistrationWaJob] Error: ' . $th->getMessage(), [
                'internet_customer_id' => $this->internetCustomerId,
            ]);
        }
    }

    private function buildMessage(InternetCustomer $customer, $settings): string
    {
        $userCustomer = $customer->userCustomer;
        $package      = $customer->internetPackage;

        $purchase = $this->purchaseId
            ? \App\Models\InternetCustomerPurchase::find($this->purchaseId)
            : null;

        $companyName = $settings['internet_company_name'] ?? 'Perusahaan';

        $name        = $userCustomer?->name        ?? $customer->name ?? '-';
        $phone       = $userCustomer?->phone_number ?? '-';
        $email       = $userCustomer?->email        ?? '-';
        $ktpNumber   = $customer->ktp_number        ?? '-';
        $address     = $customer->address           ?? '-';

        $wilayah = collect([
            $customer->subdistrict?->name,
            $customer->district?->name,
            $customer->city?->name,
            $customer->province?->name,
        ])->filter()->implode(', ') ?: '-';

        $packageName      = $package?->name      ?? '-';
        $packageBandwidth = $package?->bandwidth  ?? '-';
        $customerType     = ucfirst($customer->customer_type ?? '-');

        $registeredAt = Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB';

        $lines = [
            "🔔 *PENDAFTARAN PELANGGAN BARU*",
            "━━━━━━━━━━━━━━━━━━━━━━━",
            "",
            "📋 *INFORMASI PELANGGAN*",
            "👤 Nama       : $name",
            "📞 No. HP     : $phone",
            "📧 Email      : $email",
            "🪪 No. KTP    : $ktpNumber",
            "🏠 Alamat     : $address",
            "📍 Wilayah    : $wilayah",
            "🏢 Tipe       : $customerType",
            "",
            "━━━━━━━━━━━━━━━━━━━━━━━",
            "📦 *INFORMASI PAKET*",
            "📡 Paket      : $packageName",
            "⚡ Bandwidth  : $packageBandwidth",
            "",
            "━━━━━━━━━━━━━━━━━━━━━━━",
        ];

        // Section pembayaran: beda tampilan untuk promo vs normal
        $promo = $customer->promo;

        if ($promo) {
            $promoValue    = $promo->value ?? '-';
            $startBilling  = $customer->userCustomer?->start_billing_date
                ? Carbon::parse($customer->userCustomer->start_billing_date)->translatedFormat('d M Y')
                : '-';
            $endBilling    = $customer->userCustomer?->end_billing_date
                ? Carbon::parse($customer->userCustomer->end_billing_date)->translatedFormat('d M Y')
                : '-';

            array_push($lines,
                "🎁 *INFORMASI PROMO*",
                "🏷️ Promo      : {$promo->name}",
                "🆓 Gratis     : $promoValue bulan",
                "📅 Mulai Tagih: $startBilling",
                "🗓️ Akhir Grace: $endBilling",
                "💰 Total      : Rp 0 (Gratis)",
            );
        } elseif ($purchase) {
            $paymentMethod = match ($purchase->payment_method) {
                'manual_transfer' => 'Transfer Manual',
                'xendit'          => 'Xendit',
                'midtrans'        => 'Midtrans',
                default           => ucfirst($purchase->payment_method ?? '-'),
            };
            $paymentMonths = ($purchase->payment_months ?? '-') . ' bulan';
            $totalAmount   = 'Rp ' . number_format($purchase->amount_paid ?? 0, 0, ',', '.');
            $start         = $purchase->period_start ? Carbon::parse($purchase->period_start)->translatedFormat('d M Y') : '-';
            $end           = $purchase->period_end   ? Carbon::parse($purchase->period_end)->translatedFormat('d M Y')   : '-';

            array_push($lines,
                "💳 *INFORMASI PEMBAYARAN*",
                "💰 Total      : $totalAmount",
                "📅 Durasi     : $paymentMonths",
                "🗓️ Periode    : $start s/d $end",
                "💳 Metode     : $paymentMethod",
            );
        }

        array_push($lines,
            "",
            "━━━━━━━━━━━━━━━━━━━━━━━",
            "🕐 Daftar pada: $registeredAt",
            "",
            "_Segera tindak lanjuti pendaftaran ini._",
        );

        return implode("\n", $lines);
    }

    public function tags(): array
    {
        return ['customer-registration-wa', 'customer:' . $this->internetCustomerId];
    }
}
