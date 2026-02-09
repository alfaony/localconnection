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
use App\Helpers\InboxHelper;

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

                    // Smart billing date calculation
                    // Use period_start because it represents the actual billing day
                    $periodStartDate = Carbon::parse($purchase->period_start);
                    $maxBillingDate = config('services.internet_custom.max_billing_date', 20);
                    
                    // Get the day of month from period_start
                    $currentBillingDay = $periodStartDate->day;
                    
                    // Calculate next billing date
                    if ($currentBillingDay > $maxBillingDate) {
                        // Normalize to 1st of next month after period ends
                        $startBillingDate = $periodStartDate->copy()->addMonths($purchase->payment_months)->firstOfMonth();
                    } else {
                        // Keep same day, add payment months
                        $startBillingDate = $periodStartDate->copy()->addMonths($purchase->payment_months);
                    }
                    
                    // Calculate end billing date (grace period)
                    $gracePeriod = config('services.internet_custom.end_billing_of_days', 5);
                    $endBillingDate = $startBillingDate->copy()->addDays($gracePeriod);

                    $customerInternet->update([
                        'start_billing_date' => $startBillingDate->format('Y-m-d'),
                        'end_billing_date' => $endBillingDate->format('Y-m-d')
                    ]);

                    GenerateInternetPurchaseCouponJob::dispatch($internetCustomer->id, $purchase->id, $purchase->payment_months);

                    // Provision customer if suspended
                    // if ($internetCustomer->status == ParamSchema::SUSPENDED) {
                    //     dispatch(new ProvisionCustomerJob($internetCustomer->id));
                    // }

                    $this->afterPayment($purchase, $internetCustomer);

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

    public function handleKeloolaPay(Request $request)
    {
        Log::info('KeloolaPay webhook received', $request->all());
        $data = $request->all()?? [];

        $user = User::whereHas('role', function ($query) {
            $query->whereIn('name', [RoleSchema::SYSTEM_BOS,RoleSchema::ROOT]);
        })->first();
        
        Log::info('Xendit webhook received', $data);

        try {
            // Find purchase by external_id or invoice_id
            $externalId = isset($data['external_id']) ? array_values(explode("_", $data['external_id']))[0] : null;
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
            $callbackToken = $request->header('x-api-key');
            
            if (!$xenditService->verifyCallbackToken($callbackToken)) {
                Log::warning('Invalid Xendit callback token', [
                    'company_id' => $internetCustomer->company_id
                ]);
                return response()->json(['error' => 'Invalid callback token'], 403);
            }

            $status = $data['status'] ?? '';
            $urlResutl = '';

            // Update purchase based on status
            switch ($status) {
                case 'paid':

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

                    // Smart billing date calculation
                    // Use period_start because it represents the actual billing day
                    $periodStartDate = Carbon::parse($purchase->period_start);
                    $maxBillingDate = config('services.internet_custom.max_billing_date', 20);
                    
                    // Get the day of month from period_start
                    $currentBillingDay = $periodStartDate->day;
                    
                    // Calculate next billing date
                    if ($currentBillingDay > $maxBillingDate) {
                        // Normalize to 1st of next month after period ends
                        $startBillingDate = $periodStartDate->copy()->addMonths($purchase->payment_months)->firstOfMonth();
                    } else {
                        // Keep same day, add payment months
                        $startBillingDate = $periodStartDate->copy()->addMonths($purchase->payment_months);
                    }
                    
                    // Calculate end billing date (grace period)
                    $gracePeriod = config('services.internet_custom.end_billing_of_days', 5);
                    $endBillingDate = $startBillingDate->copy()->addDays($gracePeriod);

                    $customerInternet->update([
                        'start_billing_date' => $startBillingDate->format('Y-m-d'),
                        'end_billing_date' => $endBillingDate->format('Y-m-d')
                    ]);

                    GenerateInternetPurchaseCouponJob::dispatch($internetCustomer->id, $purchase->id, $purchase->payment_months);

                    // Provision customer if suspended
                    // if ($internetCustomer->status == ParamSchema::SUSPENDED) {
                    //     dispatch(new ProvisionCustomerJob($internetCustomer->id));
                    // }

                    Log::info('Payment confirmed for purchase', [
                        'company_id' => $internetCustomer->company_id,
                        'purchase_id' => $purchase->id
                    ]); 

                    $this->afterPayment($purchase, $internetCustomer);

                    $urlResutl = route('internet-customer.customer.show', [
                        'code' => $internetCustomer->code,
                        'status' => 'success',
                        'purchase' => $purchase->id
                    ]);

                    break;

                case 'expired':
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

                    $urlResutl = route('internet-customer.customer.show', [
                        'code' => $internetCustomer->code,
                        'status' => 'failed'
                    ]);
                    break;

                case 'failed':
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

                    $urlResutl = route('internet-customer.customer.show', [
                        'code' => $internetCustomer->code,
                        'status' => 'failed'
                    ]);

                    break;

                default:
                    $purchase->update([
                        'xendit_response' => json_encode($data),
                    ]);

                    $urlResutl = route('internet-customer.customer.show', [
                        'code' => $internetCustomer->code,
                        'status' => 'failed'
                    ]);
                    break;
            }

            $this->logging($request, 200);
            return response()->json(['success' => true, 'redirect_url' => $urlResutl]);

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

    public function afterPayment($internetPurchase, $internetCustomers)
    {
        if(!$internetCustomers->installation)
        {
            $post['status'] = ParamSchema::PROCESS_INSTALLATION;
            
            $userTechnical = optional($internetPurchase->customer->subdistrict?->coverageService?->coverageServiceOds)
            ->pluck('ods.user_assign_id')
            ->unique()
            ->all();
    
            if(count($userTechnical) > 0)
            {
                $message = "Pembayaran Langganan Internet Untuk Kode ".$internetPurchase->customer->code." Telah di Setujui Oleh Finance Silahkan segera lakukan Pemasangan";
                $directUrl = route('internet-customer.show',$internetPurchase->customer->id);
                $from = User::whereHas('role', function ($query) {
                    $query->whereIn('name', [RoleSchema::SYSTEM_BOS,RoleSchema::ROOT,RoleSchema::FINANCE]);
                })->first();

                foreach($userTechnical as $tech)
                {
                    $this->sentInbox($tech,$from->id,$message, $directUrl);
                }
            }
        }else
        {
            $post['status'] = ParamSchema::REACTIVATED;
            dispatch(new ProvisionCustomerJob($internetCustomers->id));
            \App\Jobs\SyncInstalledCustomersJob::dispatch([$internetCustomers->id]);
        }

        $internetPurchase->customer->update($post);
    }

    private function sentInbox($to,$from, $message,$directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent($to, $from, $message, $directUrl);
        return true;
    }
}