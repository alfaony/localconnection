<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Vendor;
use App\Models\ItemRequest;
use App\Models\VendorResponse;
use App\Models\ApiLog;

class WablasWebhookController extends Controller
{
    public function handle(Request $request)
    {
        ApiLog::create([
            'user_id' => "0c2db2a6-1d13-4af8-8e90-ccae7ce63812",
            'endpoint' => 'wablas',
            'method' => 'POST',
            'request_payload' => json_encode($request->all()),
            'response_payload' => json_encode(['status' => 'success']),
            'status_code' => 200,
        ]);

        return true;
        $phone = $request->input('phone'); // No HP pengirim
        $message = strtolower($request->input('message')); // Isi pesan

        // Cek apakah nomor WA ini terdaftar sebagai vendor
        $vendor = Vendor::where('nomor_wa', $phone)->first();
        if (!$vendor) 
        {
            // Jika bukan vendor terdaftar, abaikan
            return response()->json(['status' => 'unknown vendor']);
        }

        // Cek apakah mengandung format #123
        if (preg_match('/#(\d+)/', $message, $matches)) {
            $itemRequestId = (int)$matches[1];
            $itemRequest = ItemRequest::find($itemRequestId);

            if ($itemRequest) {
                // Tambahkan vendor ke vendor_potensial
                $vendorPotensial = $itemRequest->vendor_potensial ?? [];
                if (!in_array($vendor->id, $vendorPotensial)) {
                    $vendorPotensial[] = $vendor->id;
                    $itemRequest->vendor_potensial = $vendorPotensial;
                    $itemRequest->save();
                }

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
        $openRequests = ItemRequest::where('status', 'open')
            ->whereJsonContains('vendor_ids', $vendor->id)
            ->get();

        if ($openRequests->count() === 1) {
            $itemRequest = $openRequests->first();

            // Masukkan vendor ke vendor_potensial
            $vendorPotensial = $itemRequest->vendor_potensial ?? [];
            if (!in_array($vendor->id, $vendorPotensial)) {
                $vendorPotensial[] = $vendor->id;
                $itemRequest->vendor_potensial = $vendorPotensial;
                $itemRequest->save();
            }

            VendorResponse::create([
                'phone' => $phone,
                'message' => $message,
                'is_out_of_flow' => false,
                'item_request_id' => $itemRequest->id,
            ]);

            return response()->json(['status' => 'matched_1_open']);
        }

        if ($openRequests->count() > 1) {
            // Kirim permintaan klarifikasi
            $this->sendMessage($phone, "Hai kak, kamu sedang masuk ke beberapa permintaan. 🙏 Mohon balas dengan menyertakan ID seperti #123 agar bisa kami proses.");

            VendorResponse::create([
                'phone' => $phone,
                'message' => $message,
                'is_out_of_flow' => true,
                'item_request_id' => null,
            ]);

            return response()->json(['status' => 'need_id']);
        }

        // Jika tidak ada permintaan relevan → log saja, tidak perlu kirim balasan
        VendorResponse::create([
            'phone' => $phone,
            'message' => $message,
            'is_out_of_flow' => true,
            'item_request_id' => null,
        ]);

        return response()->json(['status' => 'ignored_no_open_request']);
    }

    private function sendMessage($phone, $message)
    {
        Http::withHeaders([
            'Authorization' => env('WABLAS_TOKEN'), // simpan token di .env
        ])->post('https://console.wablas.com/api/v2/send-message', [
            'phone' => $phone,
            'message' => $message,
        ]);
    }
}
