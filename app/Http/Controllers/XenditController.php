<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternetCustomerPurchase;
use App\Services\XenditService;
use App\Schemas\ParamSchema;
use App\Jobs\ProvisionCustomerJob;
use Illuminate\Support\Facades\Log;

class XenditController extends Controller
{
    /**
     * Handle Xendit webhook callback
     */
    public function webhook(Request $request)
    {
        $data = $request->all();
        
        Log::info('Xendit webhook received', $data);

        try {
            // Find purchase by external_id or invoice_id
            $externalId = $data['external_id'] ?? null;
            $invoiceId = $data['id'] ?? null;

            // Extract purchase ID from external_id (format: PURCHASE-{id}-{timestamp})
            if ($externalId) {
                preg_match('/PURCHASE-(\d+)-/', $externalId, $matches);
                $purchaseId = $matches[1] ?? null;
            }

            $purchase = InternetCustomerPurchase::where('id', $purchaseId)
                ->orWhere('xendit_invoice_id', $invoiceId)
                ->first();

            if (!$purchase) {
                Log::error('Purchase not found for Xendit callback', [
                    'external_id' => $externalId,
                    'invoice_id' => $invoiceId
                ]);
                return response()->json(['error' => 'Purchase not found'], 404);
            }

            $internetCustomer = $purchase->customer;

            // Initialize Xendit service with company ID
            $xenditService = new XenditService($internetCustomer->company_id);
            
            // Verify callback token
            $callbackToken = $request->header('x-callback-token');
            
            if (!$xenditService->verifyCallbackToken($callbackToken)) {
                Log::warning('Invalid Xendit callback token', [
                    'company_id' => $internetCustomer->company_id
                ]);
                return response()->json(['error' => 'Invalid callback token'], 403);
            }

            $status = $data['status'] ?? '';

            // Update purchase based on status
            switch ($status) {
                case 'PAID':
                case 'SETTLED':
                    $purchase->update([
                        'xendit_paid_at' => now(),
                        'xendit_payment_channel' => $data['payment_channel'] ?? null,
                        'xendit_payment_method' => $data['payment_method'] ?? null,
                        'payment_date' => now(),
                        'xendit_response' => json_encode($data),
                        'user_finance_id' => 1, // System auto-confirm
                        'confirmation_finance_at' => now(),
                    ]);

                    // Update customer status
                    $internetCustomer->update([
                        'status' => ParamSchema::ACTIVE
                    ]);

                    // Provision customer if suspended
                    if ($internetCustomer->status == ParamSchema::SUSPENDED) {
                        dispatch(new ProvisionCustomerJob($internetCustomer->id));
                    }

                    Log::info('Payment confirmed for purchase', [
                        'company_id' => $internetCustomer->company_id,
                        'purchase_id' => $purchase->id
                    ]);
                    break;

                case 'EXPIRED':
                    $purchase->update([
                        'xendit_response' => json_encode($data),
                    ]);
                    Log::info('Invoice expired for purchase', [
                        'company_id' => $internetCustomer->company_id,
                        'purchase_id' => $purchase->id
                    ]);
                    break;

                default:
                    $purchase->update([
                        'xendit_response' => json_encode($data),
                    ]);
                    break;
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Xendit webhook processing failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle payment success redirect
     */
    public function paymentSuccess($purchaseId)
    {
        $purchase = InternetCustomerPurchase::find($purchaseId);
        
        if (!$purchase) {
            return redirect()->route('public.error', ['code' => 404]);
        }

        return redirect()
            ->route('internet-customer.show', ['code' => $purchase->customer->code])
            ->with('success', 'Pembayaran berhasil! Terima kasih atas pembayaran Anda.');
    }

    /**
     * Handle payment failed redirect
     */
    public function paymentFailed($purchaseId)
    {
        $purchase = InternetCustomerPurchase::find($purchaseId);
        
        if (!$purchase) {
            return redirect()->route('public.error', ['code' => 404]);
        }

        return redirect()
            ->route('internet-customer.show', ['code' => $purchase->customer->code])
            ->with('error', 'Pembayaran gagal atau dibatalkan. Silakan coba lagi.');
    }
}