<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Models\Vendor;
use App\Models\ItemRequest;
use App\Models\ProductSupplier;
use App\Models\ApiLog;
use App\Models\VendorResponse;
use App\Models\SettingCompany;

use App\Services\Weblas\Device;
use App\Services\Weblas\Message;
use App\Services\Weblas\WablasClient;

class WablasWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            ApiLog::create([
                'user_id' => "0c2db2a6-1d13-4af8-8e90-ccae7ce63812",
                'endpoint' => 'wablas',
                'method' => 'POST',
                'request_payload' => json_encode($request->all()),
                'response_payload' => json_encode(['status' => 'success']),
                'status_code' => 200,
            ]);
    
            $phone = $request->input('phone'); // No HP pengirim
            $message = strtolower($request->input('message')); // Isi pesan
    
            // Cek apakah nomor WA ini terdaftar sebagai vendor
            $vendor = ProductSupplier::where('phone_number', $phone)->get();
            if (!$vendor || ($vendor && count($vendor) > 1)) 
            {
                return response()->json(['status' => 'unknown vendor']);
            }
    
            $vendor = $vendor->first();
    
            // Cek apakah mengandung format #123
            if (preg_match('/#(\d+)/', $message, $matches)) 
            {
                $itemRequestId = (int)$matches[1];
                $itemRequest = ItemRequest::find($itemRequestId);
    
                if ($itemRequest) {
                    // Simpan log response
                    VendorResponse::create([
                        'phone' => $phone,
                        'message' => $message,
                        'is_out_of_flow' => false,
                        'item_request_id' => $itemRequest->id,
                    ]);
    
                    return response()->json(['status' => 'matched_by_id']);
                }
            }
    
            // Jika tidak ada #id → cek apakah vendor termasuk ke dalam vendor_ids dari item open
            $openRequests = ItemRequest::where('is_open', true)->whereHas('potentialVendors', function ($query) use ($vendor) {
                $query->where('product_supplier_id', $vendor->id);
            })->get();
            
            if ($openRequests->count() === 1) {
    
                $itemRequest = $openRequests->first();
    
                $settingCompany = SettingCompany::byCompany($itemRequest->company_id)->where('menu','wablas')->get()->pluck('field_value','field_title');
                $client = new WablasClient($settingCompany['server_wablas'], $settingCompany['token_wablas'], $settingCompany['webhook_key_wablas']);
                $message = "Hai kak, terimakasih sudah menghubungi kami. Kami akan segera menghubungi vendor yang terkait. 🙏";
    
                $this->sendMessage($client, $phone, $message);
    
                VendorResponse::create([
                    'phone' => $phone,
                    'message' => $message,
                    'is_out_of_flow' => false,
                    'item_request_id' => $itemRequest->id,
                ]);
    
                $this->potentialVendor($vendor);
                
                return "Terimakasih Kak";
                // return response()->json(['status' => 'matched_1_open']);
            }
    
            if ($openRequests->count() > 1) 
            {
                // Kirim permintaan klarifikasi
                VendorResponse::create([
                    'phone' => $phone,
                    'message' => $message,
                    'is_out_of_flow' => true,
                    'item_request_id' => null,
                ]);
    
                return "Saya Disini Random";
                return response()->json(['status' => 'need_id']);
            }
    
            // Jika tidak ada permintaan relevan → log saja, tidak perlu kirim balasan
            VendorResponse::create([
                'phone' => $phone,
                'message' => $message,
                'is_out_of_flow' => true,
                'item_request_id' => null,
            ]);
    
            return response()->json(['status' => 200]);
        } catch (\Throwable $th) {
            ApiLog::create([
                'user_id' => "0c2db2a6-1d13-4af8-8e90-ccae7ce63812",
                'endpoint' => 'wablas',
                'method' => 'POST',
                'request_payload' => json_encode($request->all()),
                'response_payload' => json_encode(['status' => 'error', 'message' => $th->getMessage()]),
                'status_code' => 500,
            ]);
        }
    }

    private function sendMessage($client, $phone, $message)
    {
        $send = new Message($client);
        $send_text = $send->single_text($phone,$message);
    }

    private function potentialVendor($vendorPotensial)
    {
        $vendorPotensial->update([
            'responded' => true,
            'responded_at' => now(),
        ]);
    }
}