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
                    $products = UsedLaptop::where('company_id', $this->setting->company_id)->get();
    
                    foreach ($products as $laptop) 
                    {
                        $payload = 
                        [
                            'id' => $laptop->id,
                            'is_sold' => $laptop->is_sold,
                            'serial_number' => $laptop->serial_number,
                            'brand' => $laptop->brand,
                            'slug' => $laptop->slug,
                            'name' => $laptop->name,
                            'processor' => $laptop->processor,
                            'ram' => $laptop->ram,
                            'ssd' => $laptop->ssd,
                            'gpu' => $laptop->gpu,
                            'operating_system' => $laptop->operating_system,
                            'notes' => $laptop->notes,
                            'selling_price' => $laptop->suggested_selling_price,
                            'images' => $laptop->media()->get()->map(function ($media) {
                                return env('APP_URL') . Storage::url($media->file_path);
                            })->toArray(),
                        ];
    
                        WebhookHelper::sendWebhook($this->setting->company_id, $app, 'sync', $payload, $this->setting->id);      
                    }
                }
    
                // Tambahkan app lain di sini jika perlu
            }
        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
            Log::error($th->getMessage());
        }
    }
}