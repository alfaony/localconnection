<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternetCustomerPurchase;
use App\Services\MidtransService;
use App\Services\SubscriptionService;
use App\Schemas\ParamSchema;
use App\Jobs\ProvisionCustomerJob;
use Illuminate\Support\Facades\Log;
use App\Models\InternetCustomer;
use App\Models\Customer;
use App\Models\User;
use App\Models\ApiLog;
use App\Models\CustomerSubscription;
use App\Models\SubscriptionPayment;
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
            $orderIdRaw = $data['order_id'] ?? null;
            
            if (!$orderIdRaw) {
                Log::error('Order ID not found in Midtrans notification');
                return response()->json(['message' => 'Order ID Not Found'], 200);
            }

            $idParts = explode('_', $orderIdRaw);
            $identifier       = $idParts[0];
            $subscriptionType = $idParts[1] ?? 'internetCustomer';

            if ($subscriptionType === 'softwareSharing') {
                return $this->processSoftwareSharingWebhook($identifier, $data, $user, $request);
            } else {
                return $this->processInternetCustomerWebhook($identifier, $data, $user, $request);
            }

        } catch (\Exception $e) {
            // dd($e);
            $this->logging($request, 500, $e);

            Log::error('Midtrans webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return response()->json(['message' => 'Processing failed'], 200);
        }
    }

    /**
     * Process Internet Customer Webhook Logic
     */
    private function processInternetCustomerWebhook($orderId, $data, $user, $request)
    {
        // Parse order_id to get purchase_id (format: INT-{purchase_id}-{timestamp})
        $parts = explode('-', $orderId);
        if (count($parts) < 3 || $parts[0] !== 'INT') {
            Log::error('Invalid order_id format for Internet Customer', ['order_id' => $orderId]);
            return response()->json(['message' => 'Invalid Order ID Format'], 200);
        }

        $purchaseId = $parts[1];

        // Find the purchase
        $purchase = InternetCustomerPurchase::with(['customer.userCustomer'])->find($purchaseId);

        if (!$purchase) {
            Log::error('Purchase not found for Midtrans notification', [
                'order_id' => $orderId,
                'purchase_id' => $purchaseId,
            ]);
            return response()->json(['message' => 'Purchase Not Found'], 200);
        }

        // Validate customer exists
        $internetCustomer = $purchase->customer;
        if (!$internetCustomer) {
            Log::error('Customer not found for purchase', [
                'order_id' => $orderId,
                'purchase_id' => $purchaseId,
            ]);
            return response()->json(['message' => 'Customer Not Found'], 200);
        }

        // Validate userCustomer exists
        $customerInternet = $internetCustomer->userCustomer;
        if (!$customerInternet) {
            Log::error('UserCustomer not found for customer', [
                'order_id' => $orderId,
                'purchase_id' => $purchaseId,
                'customer_id' => $internetCustomer->id,
            ]);
            return response()->json(['message' => 'User Customer Not Found'], 200);
        }

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

        Log::info('Processing Midtrans notification (Internet Customer)', [
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
    }

    /**
     * Process Software Sharing Webhook Logic
     */
    private function processSoftwareSharingWebhook($invoiceNumber, $data, $user, $request)
    {
        // Lookup SubscriptionPayment via invoice_number
        $payment = SubscriptionPayment::with('subscription.package')
            ->where('invoice_number', $invoiceNumber)
            ->first();
        $paymentId = $payment->id;

        if (!$payment) {
            Log::error('SubscriptionPayment not found for Midtrans notification', [
                'invoice_number' => $invoiceNumber,
            ]);
            return response()->json(['message' => 'Payment Not Found'], 200);
        }

        $subscription = $payment->subscription;

        if (!$subscription) {
            Log::error('Subscription not found for payment', [
                'payment_id' => $paymentId,
            ]);
            return response()->json(['message' => 'Subscription Not Found'], 200);
        }

        // Initialize Midtrans service with company ID
        $midtransService = new MidtransService($subscription->company_id);

        // Verify notification signature
        if (!$midtransService->verifyNotification($data)) {
            Log::warning('Invalid Midtrans notification signature (Software Sharing)', [
                'company_id' => $subscription->company_id,
                'payment_id' => $paymentId,
            ]);
        }

        // Get transaction status
        $transactionStatus = $data['transaction_status'] ?? '';
        $fraudStatus = $data['fraud_status'] ?? 'accept';
        $paymentType = $data['payment_type'] ?? null;

        Log::info('Processing Midtrans notification (Software Sharing)', [
            'payment_id'         => $paymentId,
            'subscription_id'    => $subscription->id,
            'transaction_status' => $transactionStatus,
            'fraud_status'       => $fraudStatus,
            'payment_type'       => $paymentType,
        ]);

        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            if ($transactionStatus == 'capture' && $fraudStatus != 'accept') {
                return response()->json(['status' => 'ignored']);
            }

            // Update payment yang tepat (langsung dari ID, bukan cari pending terbaru)
            $payment->update([
                'status'                  => 'paid',
                'paid_at'                 => now(),
                'payment_proof'           => 'AUTO_VERIFIED',
                'notes'                   => 'Otomatis via webhook Midtrans',
                'raw_response'            => json_encode($data),
                'user_finance_id'         => $user->id,
                'confirmation_finance_at' => now(),
            ]);

            // Update subscription status and dates
            $package = $subscription->package;
            
            $tanggalMulai = $subscription->tanggal_expired && $subscription->tanggal_expired->isFuture()
                            ? $subscription->tanggal_expired
                            : now();

            $tanggalExpired = SubscriptionService::calculateExpiredDate($tanggalMulai, $package->durasi_hari ?? null);

            $subscription->update([
                'status'         => 'active',
                'payment_status' => 'paid',
                'tanggal_mulai'  => $subscription->tanggal_mulai ?? $tanggalMulai, // keep original start date; $tanggalMulai used for new/renewal
                'tanggal_expired'=> $tanggalExpired,
            ]);

            Log::info('Payment confirmed for Software Subscription', [
                'company_id'      => $subscription->company_id,
                'subscription_id' => $subscription->id,
                'payment_id'      => $paymentId,
            ]);

            $this->notifyTeamSuccess($subscription, $user);

        } elseif ($transactionStatus == 'pending') {
            $payment->update([
                'raw_response' => json_encode($data),
            ]);
        } elseif ($transactionStatus == 'deny' || $transactionStatus == 'cancel' || $transactionStatus == 'expire') {
            $payment->update([
                'status'       => 'failed',
                'raw_response' => json_encode($data),
            ]);

            Log::info('Payment ' . $transactionStatus . ' for Software Subscription', [
                'company_id'      => $subscription->company_id,
                'subscription_id' => $subscription->id,
                'payment_id'      => $paymentId,
            ]);
        }

        $this->logging($request, 200);
        return response()->json(['status' => 'success']);
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

        // Debug logging
        Log::info('Smart Billing Calculation', [
            'purchase_id' => $purchase->id,
            'period_start' => $purchase->period_start,
            'payment_months' => $purchase->payment_months,
            'current_billing_day' => $currentBillingDay,
            'max_billing_date' => $maxBillingDate,
            'normalized' => $currentBillingDay > $maxBillingDate ? 'YES' : 'NO',
            'start_billing_date' => $startBillingDate->format('Y-m-d'),
            'end_billing_date' => $endBillingDate->format('Y-m-d')
        ]);

        $customerInternet->update([
            'start_billing_date' => $startBillingDate->format('Y-m-d'),
            'end_billing_date' => $endBillingDate->format('Y-m-d')
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
     * Send inbox notifications after manual approve:
     * 1. To the customer: payment approved
     * 2. To PIC software (from software.pic relation): please follow up
     */
    protected function notifyTeamSuccess($subscription, $user)
    {
        $subscription->load(['user', 'package.software.pic', 'masterAccount.software.pic']);

        $approvedBy = $user->id;
        $customer   = $subscription->user;
        $paket      = $subscription->package->nama_paket ?? 'Langganan';

        // Resolve software object (prefer from masterAccount, fallback to package)
        $softwareModel = $subscription->masterAccount->software
                      ?? $subscription->package->software
                      ?? null;
        $softwareName  = $softwareModel->nama ?? 'Software';

        // ── 1. Notifikasi ke Pelanggan ──────────────────────────────────────
        $urlCustomer = route('customer-subscription.show', $subscription->id);
        $msgCustomer = "🎉 Selamat! Pembayaran Anda untuk paket *{$paket}* ({$softwareName}) "
                     . "telah berhasil dikonfirmasi. Langganan Anda kini sudah aktif. "
                     . "Silakan akses dashboard untuk melihat detail akses Anda.";
        $this->sentInbox($customer->id, $approvedBy, $msgCustomer, $urlCustomer);

        // ── 2. Notifikasi ke PIC Software ──────────────────────────────────
        $pic = $softwareModel ? $softwareModel->pic : null;

        if ($pic && $pic->id !== $approvedBy) {
            $urlAdmin = route('subscription.payments', $subscription->id);
            $msgAdmin = "📋 Member *{$customer->name}* telah berhasil melakukan pembayaran "
                      . "untuk paket *{$paket}* ({$softwareName}). "
                      . "Pembayaran sudah disetujui. Silakan segera tindak lanjuti dan pastikan akses member sudah aktif.";

            $this->sentInbox($pic->id, $approvedBy, $msgAdmin, $urlAdmin);

            \Log::info('Inbox notification sent to PIC', [
                'subscription_id' => $subscription->id,
                'customer_id'     => $customer->id,
                'pic_id'          => $pic->id,
            ]);
        } else {
            \Log::info('No PIC found for software, skipping PIC notification', [
                'subscription_id' => $subscription->id,
                'software'        => $softwareName,
            ]);
        }
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
