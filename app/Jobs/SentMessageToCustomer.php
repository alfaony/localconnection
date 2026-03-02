<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Models\ItemRequest;
use App\Models\SettingCompany;

use App\Services\Weblas\Device;
use App\Services\Weblas\Message;
use App\Services\Weblas\WablasClient;

class SentMessageToCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $itemRequest;

    public function __construct($itemRequest)
    {
        $this->itemRequest = $itemRequest;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $settingCompany = SettingCompany::byCompany($this->itemRequest->company_id)->where('menu','wablas')->get()->pluck('field_value','field_title');
            $client = new WablasClient($settingCompany['server_wablas'], $settingCompany['token_wablas'], $settingCompany['webhook_key_wablas']);
            if($client->status())
            {
                $userTagihan = $this->itemRequest->user;
                foreach ($potentialVendors as $potentialVendor) 
                {
                    $url = route('vendor.respond', ['id' => $potentialVendor->id, 'token' => $potentialVendor->response_token]);

                    $message = "Hai Kak {$customer->name}, kami dari Thrive ingin menginformasikan mengenai tagihan layanan internet Anda.\n\n"
                                . "📌 *Detail Tagihan:*\n"
                                . "Nama Pelanggan: {$customer->name}\n"
                                . "Layanan: Internet Rumah\n"
                                . "Periode: Agustus 2025\n"
                                . "Jumlah Tagihan: Rp {$tagihan->formatted_total}\n\n"
                                . "Untuk melakukan pembayaran atau konfirmasi, silakan klik tautan berikut:\n"
                                . "{$url}\n\n"
                                . "Mohon segera melakukan pembayaran sebelum jatuh tempo agar layanan tetap aktif.\n\n"
                                . "Terima kasih atas kepercayaannya menggunakan layanan Thrive.\n\n"
                                . "*Admin Thrive* 🙏";

                    $this->sendMessage($client, $potentialVendor->productSupplier->phone_number, $message);
                }
            }
        } catch (\Throwable $th) 
        {
            // dd($th);
            Log::error($th->getMessage());
        }
    }

    private function sendMessage($client, $phone, $message)
    {
        $send = new Message($client);
        $send_text = $send->single_text($phone,$message);
    }
}

