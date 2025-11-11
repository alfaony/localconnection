<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternetCustomerPurchase;
use App\Services\XenditService;
use App\Schemas\ParamSchema;
use App\Jobs\ProvisionCustomerJob;
use Illuminate\Support\Facades\Log;
use App\Models\InternetCustomer;
use App\Models\Customer;
use App\Models\User;
use App\Models\ApiLog;

use App\Jobs\GenerateInternetPurchaseCouponJob;
use App\Schemas\RoleSchema;

use Carbon\Carbon;
class XenditController extends Controller
{
    /**
     * Handle Xendit webhook callback
     */
    public function handle(Request $request)
    {
        $data = $request->all();

        $user = User::whereHas('role', function ($query) {
            $query->whereIn('name', [RoleSchema::SYSTEM_BOS,RoleSchema::ROOT]);
        })->first();
        
        Log::info('Xendit webhook received', $data);

        try {
            // Find purchase by external_id or invoice_id
            $externalId = $data['external_id'] ?? null;
            $purchaseId = $externalId;

            $purchase = InternetCustomerPurchase::where('id', $purchaseId)
                ->first();
            

            if (!$purchase) {
                Log::error('Purchase not found for Xendit callback', [
                    'external_id' => $externalId,
                ]);
                return response()->json(['error' => 'Purchase not found'], 404);
            }

            $internetCustomer = $purchase->customer;

            $customerInternet = $purchase->customer->userCustomer;

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
                case 'SETTLED':
                case 'PAID':

                    $purchase->update([
                        'xendit_paid_at' => now(),
                        'xendit_payment_method' => $data['payment_method'] ?? null,
                        'xendit_raw_response' => json_encode($data),
                        'user_finance_id' => $user->id, // System auto-confirm
                        'confirmation_finance_at' => now(),
                    ]);

                    // Update customer status
                    // $internetCustomer->update([
                    //     'status' => ParamSchema::REACTIVATED
                    // ]);

                    $date = Carbon::parse($purchase->period_end);

                    
                    $customerInternet->update([
                        'start_billing_date' => $date->firstOfMonth()->format('Y-m-d'),
                        'end_billing_date' => $date->addDays(config('services.internet_custom.end_billing_of_days'))->format('Y-m-d')
                    ]);

                    GenerateInternetPurchaseCouponJob::dispatch($internetCustomer->id, $purchase->id, $purchase->payment_months);

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
                        'confirmation_finance_at' => null,
                        'user_finance_id' => null,
                        'period_start' => null,
                        'period_end' => null,
                        'payment_method' => null,
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

            $this->logging($request, 200);
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            $this->logging($request, 500, $e);

            // dd($e);
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


    public function logging($request, $code, $response = null)
    {
        $user = User::whereHas('role', function ($query) {
            $query->whereIn('name', [RoleSchema::SYSTEM_BOS,RoleSchema::ROOT]);
        })->first();

        ApiLog::create([
            'user_id' => $user->id,
            'endpoint' => '/xendit/webhook',
            'method' => 'POST',
            'request_payload' => json_encode($request->all()),
            'response_payload' => json_encode($response),
            'status_code' => 200,
        ]);
    }
}