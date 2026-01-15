<?php
namespace App\Jobs;

use App\Jobs\SendProductToWebhookJob;
use App\Models\UsedLaptop;
use App\Helpers\WebhookHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class SendWebhookSettingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $setting;

    public function __construct($setting)
    {
        $this->setting = $setting;
    }

    public function handle()
    {
        try {
            foreach ($this->setting->selected_apps as $app) {
                if ($app === 'used_laptops') {
                    // ✅ Hanya sync laptop yang memiliki rack (QC PASSED) dan belum terjual
                    $products = UsedLaptop::where('is_sold', 0)
                        ->where('company_id', $this->setting->company_id)
                        ->whereNotNull('is_sold')
                        ->whereNotNull('rack_id') // Hanya yang sudah ada rack (QC PASSED)
                        ->with(['media', 'rack']) // Eager load untuk efisiensi
                        ->get();
    
                    foreach ($products as $laptop) 
                    {
                        // Gunakan UsedLaptopResource untuk konsistensi payload
                        $payload = (new \App\Http\Resources\UsedLaptopResource($laptop))->resolve();
    
                        WebhookHelper::sendWebhook($this->setting->company_id, $app, 'sync', $payload, $this->setting->id);      
                    }
                }
    
                // Tambahkan app lain di sini jika perlu
            }
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error($th->getMessage());
        }
    }
}