<?php

// app/Http/Controllers/WablasWebhookController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\ItemRequest;
use App\Models\VendorResponse;
use Illuminate\Support\Facades\Http;

class WablasWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $phone = $request->input('phone');
        $message = strtolower($request->input('message'));

        $vendor = Vendor::where('nomor_wa', $phone)->first();
        if (!$vendor) {
            return response()->json(['status' => 'unknown vendor']);
        }

        // Cek apakah ada kode #123 di pesan
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

                // Simpan respons
                VendorResponse::create([
                    'phone' => $phone,
                    'message' => $message,
                    'is_out_of_flow' => false,
                    'item_request_id' => $itemRequestId,
                ]);

                return response()->json(['status' => 'success']);
            }
        }

        // Tidak pakai #id, cek apakah pernah diingatkan
        $lastResponse = VendorResponse::where('phone', $phone)->latest()->first();

        if (!$lastResponse || !$lastResponse->sudah_ingatkan) {
            // Kirim pengingat
            $this->sendMessage($phone, "Hai kak! Mohon balas dengan kode seperti #123 agar bisa kami proses ya. 🙏");

            VendorResponse::create([
                'phone' => $phone,
                'message' => $message,
                'is_out_of_flow' => true,
                'sudah_ingatkan' => true,
                'item_request_id' => null,
            ]);

            return response()->json(['status' => 'reminded']);
        } else {
            // Sudah diingatkan sebelumnya, kirim penutup
            $this->sendMessage($phone, "Terima kasih! Karena tidak ada kode permintaan, kami anggap percakapan ini selesai. 🙏");

            VendorResponse::create([
                'phone' => $phone,
                'message' => $message,
                'is_out_of_flow' => true,
                'sudah_ingatkan' => false,
                'item_request_id' => null,
            ]);

            return response()->json(['status' => 'closed']);
        }
    }

    private function sendMessage($phone, $message)
    {
        Http::withHeaders([
            'Authorization' => env('WABLAS_TOKEN'),
        ])->post('https://console.wablas.com/api/v2/send-message', [
            'phone' => $phone,
            'message' => $message,
        ]);
    }
}
