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
                $potentialVendors = $this->itemRequest->potentialVendors()->get();
                foreach ($potentialVendors as $potentialVendor) 
                {
                    $url = route('vendor.respond', ['id' => $potentialVendor->id, 'token' => $potentialVendor->response_token]);

                    $message ="Hai Kak, perkenalkan kami dari Thrive.\n\n" 
                        ."Kami membutuhkan barang berikut:\n\n"
                        . "Nama: {$this->itemRequest->item_name}\n"
                        . "Qty: {$this->itemRequest->qty}\n"
                        . "Estimasi Harga: {$this->itemRequest->price_with_format}\n\n"
                        . "Apakah di toko {$potentialVendor->productSupplier->store_name} tersedia untuk produk tersebut?\n"
                        . "Jika tersedia, mohon konfirmasinya ya Kak.\n\n"
                        . "Untuk melakukan penawaran, silakan klik link di bawah ini:\n"
                        . "{$url}\n\n"
                        . "Terima kasih🙏😊";

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

