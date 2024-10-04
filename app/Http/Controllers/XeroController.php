<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\XeroService;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class XeroController extends Controller
{
    protected $xeroService;

    public function __construct(XeroService $xeroService)
    {
        $this->xeroService = $xeroService;
    }

    public function connect()
    {
        return $this->xeroService->connect();
    }

    public function disconnect()
    {
        $this->xeroService->disconnect();
        return true;
    }

    public function callback(Request $request)
    {
        $this->xeroService->handleCallback($request);
        return redirect()->route('xero.invoices')->with('success', 'Connected to Xero.');
    }

    public function handleWebhook(Request $request)
    {
        // Ambil raw payload dari request
        $payload = $request->getContent();

        // Ambil webhook signing key dari environment (.env file)
        $xeroSigningKey = env('XERO_WEBHOOK_KEY'); // Isi key dari Xero Developer Portal

        // Verifikasi signature dari Xero
        $signature = base64_encode(hash_hmac('sha256', $payload, $xeroSigningKey, true));
        $xeroSignature = $request->header('X-Xero-Signature');

        if ($signature !== $xeroSignature) {
            // Log jika signature tidak cocok
            Log::warning('Invalid Xero webhook signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Proses event dari payload Xero
        $events = json_decode($payload, true)['events'];
        foreach ($events as $event) {
            // Contoh: cek apakah ini update invoice
            if ($event['eventType'] == 'UPDATE' && $event['resourceType'] == 'INVOICE') {
                // Cari invoice di database berdasarkan ID Xero
                $invoiceId = $event['resourceId'];
                $invoice = Invoice::where('xero_invoice_id', $invoiceId)->first();

                if ($invoice) {
                    // Panggil API Xero untuk mengambil detail invoice terbaru
                    $xeroInvoice = Xero::invoices()->find($invoiceId);

                    // Update data invoice di aplikasi Laravel
                    $invoice->total = $xeroInvoice->total;
                    $invoice->status = $xeroInvoice->status;
                    $invoice->save();
                }
            }
        }

        // Kembalikan respon sukses ke Xero
        return response()->json(['status' => 'success'], 200);
    }

}
