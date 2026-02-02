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

            
            $check = InternetCustomerPurchase::where('internet_customer_id', $internetCustomer->id)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->first();
            if (!$check) 
            {
                $this->customer->internetCustomer->update([
                    'is_paid' => false,
                    'status' => ParamSchema::WAITING_PAYMENT_SUBSCRIPTION
                ]);

                $check = InternetCustomerPurchase::create([
                    'internet_package_id' => $internetCustomer->internetPackage->id,
                    'amount_paid' => $internetCustomer->internetPackage->price_nett ?? 0,
                    'internet_customer_id' => $internetCustomer->id,
                ]);
            }else
            {
                if(!$internetCustomer->installation && $check->end_billing_date < Carbon::now()->format('Y-m-d') && ($check->xendit_paid_at || $check->confirmation_finance_at))
                {
                    $this->customer->internetCustomer->update([
                        'is_paid' => true,
                        'status' => ParamSchema::PROCESS_INSTALLATION
                    ]);
                }

                if($internetCustomer->installation && $check->end_billing_date < Carbon::now()->format('Y-m-d') && ($check->xendit_paid_at || $check->confirmation_finance_at))
                {
                    $this->customer->internetCustomer->update([
                        'is_paid' => true,
                        'status' => ParamSchema::REACTIVATED
                    ]);
                }
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
                $dateJatuhTempo = Carbon::parse($customer->internetCustomer->end_billing_date)->format('d') . ' ' . Carbon::parse($customer->internetCustomer->end_billing_date)->locale('id')->monthName . ' ' . Carbon::parse($customer->internetCustomer->end_billing_date)->year;

                $message = "Hai Kak {$customer->name}, kami dari Hikari Net ingin menginformasikan mengenai tagihan layanan internet Anda.\n\n"
                            . "📌 *Detail Tagihan:*\n"
                            . "Nama Pelanggan: {$customer->name}\n"
                            . "Layanan Paket: {$customer->internetCustomer->internetPackage->name}\n"
                            . "Periode: " . Carbon::now()->locale('id')->monthName . " " . Carbon::now()->year . "\n"
                            . "Jumlah Tagihan: Rp " . number_format($customer->internetCustomer->internetPackage->price_nett, 2, ',', '.') . "\n\n"
                            . "Untuk melakukan pembayaran atau konfirmasi, silakan klik tautan berikut:\n\n"
                            . "{$url}\n\n"
                            . "Mohon segera melakukan pembayaran sebelum jatuh tempo pada tanggal {$dateJatuhTempo} agar layanan tetap aktif.\n\n"
                            . "Terima kasih atas kepercayaannya menggunakan layanan Hikari Net.\n\n"
                            . "*Admin Hikari Net* 🙏";

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
