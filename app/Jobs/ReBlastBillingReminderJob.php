<?php

namespace App\Jobs;

use App\Models\UserCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\SettingCompany;
use App\Services\Weblas\Message;
use App\Services\Weblas\WablasClient;

class ReBlastBillingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $customer;

    public function __construct(UserCustomer $customer)
    {
        $this->customer = $customer;
    }

    public function handle()
    {
        try {
            $internetCustomer = $this->customer->internetCustomer;

            if (!$internetCustomer) {
                Log::warning("ReBlast: Internet customer not found for UserCustomer ID: {$this->customer->id}");
                return;
            }

            // Hitung daysBeforeDue dari end_billing_date (sama seperti sistem asli)
            $today         = Carbon::today();
            $endBillingDate = Carbon::parse($this->customer->end_billing_date)->startOfDay();
            $daysBeforeDue  = (int) $today->diffInDays($endBillingDate, false);
            $daysBeforeDue  = max(0, $daysBeforeDue);

            // dd($daysBeforeDue, $internetCustomer->name);
            $settingCompany = SettingCompany::byCompany($internetCustomer->company_id)
                ->get()
                ->pluck('field_value', 'field_title');

            if (
                !empty($settingCompany['server_wablas']) &&
                !empty($settingCompany['token_wablas'])
            ) {
                $this->sentWaReBlast($settingCompany, $this->customer, $daysBeforeDue);
                Log::info("ReBlast: WA sent to customer {$internetCustomer->code} (daysBeforeDue={$daysBeforeDue})");
            } else {
                Log::warning("ReBlast: Wablas tidak dikonfigurasi untuk company: {$internetCustomer->company_id}");
            }

        } catch (\Throwable $th) {
            Log::error("ReBlast: Gagal kirim WA: " . $th->getMessage(), [
                'customer_id' => $this->customer->id ?? null,
                'trace'       => $th->getTraceAsString(),
            ]);
        }
    }

    private function sentWaReBlast($settingCompany, $customer, $daysBeforeDue)
    {
        $client = new WablasClient(
            $settingCompany['server_wablas'],
            $settingCompany['token_wablas'],
            $settingCompany['webhook_key_wablas'] ?? null
        );

        if (!$client->status()) {
            Log::error("ReBlast: Wablas client status check failed");
            return;
        }

        $internetCustomer = $customer->internetCustomer;

        $settingKey = match ((int) $daysBeforeDue) {
            0       => 'internet_remainder_billing_0',
            1       => 'internet_remainder_billing_1',
            3       => 'internet_remainder_billing_3',
            5       => 'internet_remainder_billing',
        };

        $template = html_to_wa($settingCompany[$settingKey] ?? '');

        if (empty($template)) {
            Log::info("ReBlast: Template kosong untuk key={$settingKey}, skip", [
                'customer_code' => $internetCustomer->code,
            ]);
            return;
        }

        $endBillingDate = Carbon::parse($customer->end_billing_date);
        $dateJatuhTempo = $endBillingDate->format('d')
            . ' ' . $endBillingDate->locale('id')->monthName
            . ' ' . $endBillingDate->year;

        $url     = route('internet-customer.customer.show', $internetCustomer->code);
        $priceData = $internetCustomer->internetPackage->getPriceForRegion(
            $internetCustomer->province_id,
            $internetCustomer->city_id,
            $internetCustomer->district_id,
            $internetCustomer->subdistrict_id
        );
        $message = strtr($template, [
            '{{nama}}'        => $customer->name,
            '{{kode}}'        => $internetCustomer->code,
            '{{paket}}'       => $internetCustomer->internetPackage->name,
            '{{jatuh_tempo}}' => $dateJatuhTempo,
            '{{tagihan}}'     => 'Rp. ' . number_format($priceData['price_nett'], 2, ',', '.'),
            '{{url}}'         => $url,
        ]);

        $send     = new Message($client);
        $response = $send->single_text($customer->phone_number, $message);

        \App\Models\WablasLog::record(
            source:   'internet_customer',
            sourceId: $internetCustomer->id,
            phone:    $customer->phone_number,
            message:  $message,
            response: $response ?? [],
            type:     'text'
        );
    }

    public function tags()
    {
        return ['re-blast-billing', 'customer:' . $this->customer->id];
    }
}
