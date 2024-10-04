<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use App\Models\ApiLog;
use App\Models\User;
use Xero;

class XeroWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Ambil payload mentah dan Webhook Signing Key dari environment
        $payload = $request->getContent();
        $xeroSigningKey = config('xero.webhookKey');
        $calculatedSignature = base64_encode(hash_hmac('sha256', $payload, $xeroSigningKey, true));
        $xeroSignature = $request->header('X-Xero-Signature');
        $user = User::where('name','root')->first();

        // Verifikasi signature
        if ($calculatedSignature !== $xeroSignature) 
        {
            Log::warning('Invalid Xero webhook signature');

            // Log kesalahan
            ApiLog::create([
                'user_id' => $user->id,
                'endpoint' => '/webhook/xero',
                'method' => 'POST',
                'request_payload' => json_encode($request->all()),
                'response_payload' => json_encode(['error' => 'Invalid signature']),
                'status_code' => 401,
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Proses event webhook dari Xero
        $events = json_decode($payload, true)['events'];

        foreach ($events as $event) 
        {
            if ($event['eventType'] === 'UPDATE' && $event['eventCategory'] === 'INVOICE') {
                $invoiceId = $event['resourceId'];
                $this->updateInvoiceFromXero($invoiceId);
            }
        }
        // Log sukses
        ApiLog::create([
            'user_id' => $user->id,
            'endpoint' => '/webhook/xero',
            'method' => 'POST',
            'request_payload' => json_encode($request->all()),
            'response_payload' => json_encode(['status' => 'success']),
            'status_code' => 200,
        ]);

        return response()->json(['status' => 'success'], 200);
    }

    protected function updateInvoiceFromXero($invoiceId)
    {
        try {
            // Ambil detail invoice dari Xero menggunakan SDK atau API Xero
            $xeroInvoice = Xero::invoices()->find($invoiceId);

            // Cari invoice di database berdasarkan `xero_invoice_id`
            $invoice = Invoice::where('invoice_xero_id', $invoiceId)->first();

            if ($invoice) {
                // Update data invoice di database dengan data dari Xero
                $invoice->status = $xeroInvoice['Status'];
                $invoice->save();
            } else {
                Log::info("Invoice with ID {$invoiceId} not found in local database.");
            }
        } catch (\Exception $e) {
            Log::error("Failed to update invoice from Xero: " . $e->getMessage());
        }
    }
}
