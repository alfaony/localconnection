<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\SettingCompany;
use App\Http\Controllers\Controller;

class EkycController extends Controller
{
    public function receiveKtpResult(Request $request)
    {
        try {
            $tokenDb = SettingCompany::where('menu', 'n8n')
                ->where('field_title', 'n8n_webhook_token')
                ->value('field_value');

            if (!$tokenDb || $request->api_key !== $tokenDb) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            Cache::put('ktp_scan_result_'.$request->session_id, [
                'name'       => $request->name,
                'ktp_number' => $request->ktp_number,
                'address'    => $request->address,
            ], now()->addMinutes(10));

            return response()->json(['message' => 'OK']);

        } catch (\Throwable $e) {
            Log::error('Gagal terima hasil scan KTP dari N8N', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['message' => 'OK']);
        }
    }
}
