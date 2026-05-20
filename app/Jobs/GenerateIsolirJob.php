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

            $settingCompany = SettingCompany::byCompany($internetCustomer->company_id)->where('menu','wablas')->get()->pluck('field_value','field_title');
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
            $client = new WablasClient($settingCompany['server_wablas'], $settingCompany['token_wablas'], $settingCompany['webhook_key_wablas']);
            if($client->status())
            {   
                $url = route('internet-customer.customer.show', $customer->internetCustomer->code);
                $dateJatuhTempo = Carbon::parse($customer->end_billing_date)->format('d') . ' ' . Carbon::parse($customer->end_billing_date)->locale('id')->monthName . ' ' . Carbon::parse($customer->end_billing_date)->year();
                $tutorialPayment = config('services.internet_custom.tutorial_payment');

                $message = "*Pemberitahuan Penangguhan Layanan Internet*\n\n"
                            . "*Yth. Bapak/Ibu {$customer->name},*\n"
                            . "Dengan berat hati kami informasikan bahwa layanan internet Anda telah ditangguhkan dengan detail sebagai berikut:\n\n"
                            . "ID Pelanggan: {$customer->internetCustomer->code}\n"
                            . "Paket Layanan: {$customer->internetCustomer->internetPackage->name}\n"
                            . "Jatuh Tempo Pembayaran: {$dateJatuhTempo}\n"
                            . "Total Tagihan: Rp. " . number_format($customer->internetCustomer->internetPackage->price_nett, 2, ',', '.') . "\n\n"
                            . "⛔ Layanan internet Anda telah ditangguhkan karena belum adanya pembayaran hingga tanggal jatuh tempo.\n\n"
                            . "Untuk mengaktifkan kembali layanan, silakan segera lakukan pembayaran melalui tautan berikut:\n\n"
                            . "{$url}\n\n"
                            . "{$tutorialPayment}"
                            . "Setelah pembayaran dikonfirmasi, layanan Anda akan segera diaktifkan kembali.\n\n"
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
            // dd($th);
            \Log::error($th->getMessage());
        }
    }

    private function sendMessage($client, $phone, $message)
    {
        $send = new Message($client);
        $send_text = $send->single_text($phone,$message);

        return $send_text;
    }
}

