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
            ->orderBy('created_at', 'desc')
            ->first();

            if (!$check || $check->payment_method == ParamSchema::EXPIRED) 
            {
                $this->customer->internetCustomer->update([
                    'is_paid' => false,
                    'status' => ParamSchema::WAITING_PAYMENT_SUBSCRIPTION
                ]);

                $purchase = InternetCustomerPurchase::create([
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
                $dateJatuhTempo = Carbon::parse($customer->end_billing_date)->format('d') . ' ' . Carbon::parse($customer->end_billing_date)->locale('id')->monthName . ' ' . Carbon::parse($customer->end_billing_date)->year;

                $tutorialPayment = config('services.internet_custom.tutorial_payment');

                $message = "*Ringkasan Tagihan Layanan Internet*\n\n"
                            . "*Yth. Bapak/Ibu {$customer->name},*\n"
                            . "Berikut ini adalah pengingat tagihan Anda dengan detail sebagai berikut:\n\n"
                            . "Nomor Faktur: " . ($customer->internetCustomer->purchases()->latest()->first()->code ?? '-') . "\n"
                            . "ID Pelanggan: {$customer->internetCustomer->code}\n"
                            . "Paket Layanan: {$customer->internetCustomer->internetPackage->name}\n"
                            . "Jatuh Tempo Pembayaran: {$dateJatuhTempo}\n"
                            . "Total Tagihan: Rp. " . number_format($customer->internetCustomer->internetPackage->price_nett, 2, ',', '.') . "\n\n"
                            . "⛔ Mohon segera lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari penghentian layanan dan pemutusan koneksi internet.\n\n"
                            . "Untuk melakukan pembayaran atau konfirmasi, silakan klik tautan berikut:\n\n"
                            . "{$url}\n\n"
                            . "{$tutorialPayment}"
                            . "Terima kasih atas perhatian dan kerjasama nya 🙏.\n\n"
                            . "*Hormat kami,*\n"
                            . "*Hikarinet by KAILI Global*";

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
        } catch (\Throwable $th) 
        {
            Log::error($th->getMessage());
        }
    }

    private function sendMessage($client, $phone, $message)
    {
        $send = new Message($client);
        return $send->single_text($phone,$message);
    }
}
