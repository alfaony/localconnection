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

class GenerateBillingJob implements ShouldQueue
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

            $this->customer->internetCustomer->update([
                'is_paid' => false,
                'status' => ParamSchema::WAITING_PAYMENT_SUBSCRIPTION
            ]);

            $check = InternetCustomerPurchase::where('internet_customer_id', $internetCustomer->id)
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->first();
            if (!$check) 
            {
                $check = InternetCustomerPurchase::create([
                    'amount_paid' => $internetCustomer->internetPackage->price_nett ?? 0,
                    'internet_customer_id' => $internetCustomer->id,
                ]);
            }

            $settingCompany = SettingCompany::byCompany($internetCustomer->company_id)->where('menu','wablas')->get()->pluck('field_value','field_title');
            if($settingCompany['server_wablas'] && $settingCompany['token_wablas'])
            {
                $this->sentWa($settingCompany, $this->customer);
            }

            DB::commit();
        } catch (\Throwable $th) {
            // dd($th);
            DB::rollBack();
            \Log::error("Gagal buat tagihan otomatis: " . $th->getMessage());
        }
    }

    private function sentWa($settingCompany, $customer)
    {
        try {
            $client = new WablasClient($settingCompany['server_wablas'], $settingCompany['token_wablas'], $settingCompany['webhook_key_wablas']);
            if($client->status())
            {   
                
                $url = route('internet-customer.customer.show', $customer->internetCustomer->code);

                $message = "Hai Kak {$customer->name}, kami dari Hikari ingin menginformasikan mengenai tagihan layanan internet Anda.\n\n"
                            . "📌 *Detail Tagihan:*\n"
                            . "Nama Pelanggan: {$customer->name}\n"
                            . "Layanan Paket: {$customer->internetCustomer->internetPackage->name}\n"
                            . "Periode: " . Carbon::now()->locale('id')->monthName . " " . Carbon::now()->year . "\n"
                            . "Jumlah Tagihan: Rp {$customer->internetCustomer->internetPackage->name}\n\n"
                            . "Untuk melakukan pembayaran atau konfirmasi, silakan klik tautan berikut:\n"
                            . "{$url}\n\n"
                            . "Mohon segera melakukan pembayaran sebelum jatuh tempo agar layanan tetap aktif.\n\n"
                            . "Terima kasih atas kepercayaannya menggunakan layanan Hikari.\n\n"
                            . "*Admin Hikari* 🙏";

                $this->sendMessage($client, $customer->phone_number, $message);
            }
        } catch (\Throwable $th) 
        {
            Log::error($th->getMessage());
        }
    }

    private function sendMessage($client, $phone, $message)
    {
        $send = new Message($client);
        $send_text = $send->single_text($phone,$message);
    }
}
