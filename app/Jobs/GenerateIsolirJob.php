<?php

namespace App\Jobs;

use App\Models\UserCustomer;
use App\Models\InternetCustomerPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Schemas\ParamSchema;

use App\Models\SettingCompany;

use App\Services\Weblas\Device;
use App\Services\Weblas\Message;
use App\Services\Weblas\WablasClient;

class GenerateIsolirJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $customer;

    public function __construct(UserCustomer $customer)
    {
        $this->customer = $customer;
    }

    public function handle()
    {
        $internetCustomer = $this->customer->internetCustomer;

        DB::beginTransaction();
        try {
            $internetCustomer->update([
                'is_paid' => false,
                'status' => ParamSchema::SUSPENDED
            ]);

            DB::commit();

            dispatch(new ProvisionCustomerJob($internetCustomer->id));

            $settingCompany = SettingCompany::byCompany($internetCustomer->company_id)->get()->pluck('field_value','field_title');
            if($settingCompany['server_wablas'] && $settingCompany['token_wablas'])
            {
                $this->sentWa($settingCompany, $this->customer);
            }
        } catch (\Throwable $th) 
        {
            // dd($th);
            DB::rollBack();
            \Log::error("Gagal buat tagihan otomatis: " . $th->getMessage());
        }
    }

    private function sentWa($settingCompany, $customer)
    {
        try {
            $internetSetting = SettingCompany::byCompany($customer->internetCustomer->company_id)
                // ->where('menu', 'wablas')
                ->get()
                ->pluck('field_value', 'field_title');

            $template = html_to_wa($internetSetting['internet_remainder_billing_isolir'] ?? '');

            if (empty($template)) {
                \Log::info('[GenerateIsolirJob] Template isolir kosong, skip kirim WA', [
                    'customer_code' => $customer->internetCustomer->code,
                ]);
                return;
            }

            $client = new WablasClient($settingCompany['server_wablas'], $settingCompany['token_wablas'], $settingCompany['webhook_key_wablas']);
            if ($client->status()) {
                $url            = route('internet-customer.customer.show', $customer->internetCustomer->code);
                $dateJatuhTempo = Carbon::parse($customer->end_billing_date)->format('d')
                    . ' ' . Carbon::parse($customer->end_billing_date)->locale('id')->monthName
                    . ' ' . Carbon::parse($customer->end_billing_date)->year;
                $tutorialPayment = config('services.internet_custom.tutorial_payment');

                $internetCustomer = $customer->internetCustomer;
                $priceData = $internetCustomer->internetPackage->getPriceForRegion(
                    $internetCustomer->province_id,
                    $internetCustomer->city_id,
                    $internetCustomer->district_id,
                    $internetCustomer->subdistrict_id
                );

                $message = strtr($template, [
                    '{{nama}}'        => $customer->name,
                    '{{kode}}'        => $customer->internetCustomer->code,
                    '{{paket}}'       => $customer->internetCustomer->internetPackage->name,
                    '{{jatuh_tempo}}' => $dateJatuhTempo,
                    '{{tagihan}}'     => 'Rp. ' . number_format($priceData['price_nett'], 2, ',', '.'),
                    '{{url}}'         => $url,
                    '{{tutorial}}'    => $tutorialPayment ?? '',
                ]);

                $response = $this->sendMessage($client, $customer->phone_number, $message);

                \App\Models\WablasLog::record(
                    source: 'internet_customer',
                    sourceId: $customer->internetCustomer->id,
                    phone: $customer->phone_number,
                    message: $message,
                    response: $response ?? [],
                    type: 'text'
                );
            }
        } catch (\Throwable $th) {
            \Log::error('[GenerateIsolirJob] sentWa error: ' . $th->getMessage());
        }
    }

    private function sendMessage($client, $phone, $message)
    {
        $send = new Message($client);
        $send_text = $send->single_text($phone,$message);

        return $send_text;
    }
}

