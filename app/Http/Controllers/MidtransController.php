<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternetCustomerPurchase;
use App\Services\MidtransService;
use App\Schemas\ParamSchema;
use App\Jobs\ProvisionCustomerJob;
use Illuminate\Support\Facades\Log;
use App\Models\InternetCustomer;
use App\Models\User;
use App\Models\ApiLog;
use App\Helpers\InboxHelper;
use App\Jobs\GenerateInternetPurchaseCouponJob;
use App\Schemas\RoleSchema;
use Carbon\Carbon;

class MidtransController extends Controller
{
    /**
     * Handle Midtrans webhook notification
     */
    public function handleNotification(Request $request)
    {   
        $data = $request->all();

        $user = User::whereHas('role', function ($query) {
            $query->whereIn('name', [RoleSchema::SYSTEM_BOS, RoleSchema::ROOT]);
        })->first();

        Log::info('Midtrans webhook received', $data);

        try {
            // Extract order_id from notification
            $orderId = $data['order_id'] ?? null;
            
            if (!$orderId) {
                Log::error('Order ID not found in Midtrans notification');
                return response()->json(['error' => 'Order ID not found'], 400);
            }

            // Parse order_id to get purchase_id (format: INT-{purchase_id}-{timestamp})
            $parts = explode('-', $orderId);
            if (count($parts) < 3 || $parts[0] !== 'INT') {
                Log::error('Invalid order_id format', ['order_id' => $orderId]);
                return response()->json(['error' => 'Invalid order ID format'], 400);
            }

            $purchaseId = $parts[1];

            // Find the purchase
            $purchase = InternetCustomerPurchase::find($purchaseId);

            if (!$purchase) {
                Log::error('Purchase not found for Midtrans notification', [
                    'order_id' => $orderId,
                    'purchase_id' => $purchaseId,
                ]);
                return response()->json(['error' => 'Purchase not found'], 404);
            }

            $internetCustomer = $purchase->customer;
            $customerInternet = $purchase->customer->userCustomer;

            // Initialize Midtrans service with company ID
            $midtransService = new MidtransService($internetCustomer->company_id);

            // Verify notification signature
            if (!$midtransService->verifyNotification($data)) {
                Log::warning('Invalid Midtrans notification signature', [
                    'company_id' => $internetCustomer->company_id,
                    'order_id' => $orderId
                ]);
                return response()->json(['error' => 'Invalid signature'], 403);
            }

            // Get transaction status
            $transactionStatus = $data['transaction_status'] ?? '';
            $fraudStatus = $data['fraud_status'] ?? 'accept';
            $paymentType = $data['payment_type'] ?? null;

            Log::info('Processing Midtrans notification', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'payment_type' => $paymentType
            ]);

            // Process based on transaction status
            if ($transactionStatus == 'capture') {
                // For credit card transactions
                if ($fraudStatus == 'accept') {
                    // Payment success
                    $this->processSuccessfulPayment($purchase, $internetCustomer, $customerInternet, $data, $user, $paymentType);
                }
            } elseif ($transactionStatus == 'settlement') {
                // Payment success (for non-credit card)
                $this->processSuccessfulPayment($purchase, $internetCustomer, $customerInternet, $data, $user, $paymentType);
            } elseif ($transactionStatus == 'pending') {
                // Payment pending
                $purchase->update([
                    'midtrans_raw_response' => $data,
                ]);
                Log::info('Payment pending for purchase', [
                    'company_id' => $internetCustomer->company_id,
                    'purchase_id' => $purchase->id
                ]);
            } elseif ($transactionStatus == 'deny' || $transactionStatus == 'cancel' || $transactionStatus == 'expire') {
                // Payment failed/cancelled/expired
                $purchase->update([
                    'midtrans_raw_response' => $data,
                    'confirmation_finance_at' => null,
                    'user_finance_id' => null,
                ]);
                Log::info('Payment ' . $transactionStatus . ' for purchase', [
                    'company_id' => $internetCustomer->company_id,
                    'purchase_id' => $purchase->id,
                    'status' => $transactionStatus
                ]);
            }

            $this->logging($request, 200);
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            $this->logging($request, 500, $e);

            Log::error('Midtrans webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Process successful payment
     */
    private function processSuccessfulPayment($purchase, $internetCustomer, $customerInternet, $data, $user, $paymentType)
    {
        $purchase->update([
            'midtrans_paid_at' => now(),
            'midtrans_payment_type' => $paymentType,
            'midtrans_raw_response' => $data,
            'user_finance_id' => $user->id, // System auto-confirm
            'confirmation_finance_at' => now(),
        ]);

        $date = Carbon::parse($purchase->period_end);

        $customerInternet->update([
            'start_billing_date' => $date->firstOfMonth()->format('Y-m-d'),
            'end_billing_date' => $date->addDays(config('services.internet_custom.end_billing_of_days'))->format('Y-m-d')
        ]);

        GenerateInternetPurchaseCouponJob::dispatch($internetCustomer->id, $purchase->id, $purchase->payment_months);

        $this->afterPayment($purchase, $internetCustomer);

        Log::info('Payment confirmed for purchase', [
            'company_id' => $internetCustomer->company_id,
            'purchase_id' => $purchase->id,
            'payment_type' => $paymentType
        ]);
    }

    /**
     * Log API request
     */
    public function logging($request, $code, $response = null)
    {
        $user = User::whereHas('role', function ($query) {
            $query->whereIn('name', [RoleSchema::SYSTEM_BOS, RoleSchema::ROOT]);
        })->first();

        ApiLog::create([
            'user_id' => $user->id,
            'endpoint' => '/midtrans/notification',
            'method' => 'POST',
            'request_payload' => json_encode($request->all()),
            'response_payload' => json_encode($response),
            'status_code' => $code,
        ]);
    }

    /**
     * Handle post-payment actions
     */
    public function afterPayment($internetPurchase, $internetCustomers)
    {
        if (!$internetCustomers->installation) {
            $post['status'] = ParamSchema::PROCESS_INSTALLATION;

            $userTechnical = optional($internetPurchase->customer->subdistrict?->coverageService?->coverageServiceOds)
                ->pluck('ods.user_assign_id')
                ->unique()
                ->all();

            if (count($userTechnical) > 0) {
                $message = "Pembayaran Langganan Internet Untuk Kode " . $internetPurchase->customer->code . " Telah di Setujui Oleh Finance Silahkan segera lakukan Pemasangan";
                $directUrl = route('internet-customer.show', $internetPurchase->customer->id);
                $from = User::whereHas('role', function ($query) {
                    $query->whereIn('name', [RoleSchema::SYSTEM_BOS, RoleSchema::ROOT, RoleSchema::FINANCE]);
                })->first();

                foreach ($userTechnical as $tech) {
                    $this->sentInbox($tech, $from->id, $message, $directUrl);
                }
            }
        } else {
            $post['status'] = ParamSchema::REACTIVATED;
            dispatch(new ProvisionCustomerJob($internetCustomers->id));
            \App\Jobs\SyncInstalledCustomersJob::dispatch([$internetCustomers->id]);
        }

        $internetPurchase->customer->update($post);
    }

    /**
     * Send inbox notification
     */
    private function sentInbox($to, $from, $message, $directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent($to, $from, $message, $directUrl);
        return true;
    }
}
