<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternetCustomerPurchase;
use App\Services\XenditService;
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
use App\Jobs\SendPaymentSuccessWaJob;
use App\Schemas\RoleSchema;
use App\Models\SettingCompany;
use App\Services\Weblas\WablasClient;
use App\Services\Weblas\Message as WablasMessage;

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

                    SendPaymentSuccessWaJob::dispatch($purchase->id);

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
        $data = $request->all() ?? [];

        $user = User::whereHas('role', function ($query) {
            $query->whereIn('name', [RoleSchema::SYSTEM_BOS, RoleSchema::ROOT]);
        })->first();
        
        try {
            // Find purchase by external_id
            $externalIdRaw = $data['external_id'] ?? null;
            if (!$externalIdRaw) {
                Log::error('Missing external_id in Xendit callback');
                return response()->json(['error' => 'Missing external_id'], 400);
            }

            // Split external_id format: e.g. "INT-123_internetCustomer" or "SUB-abc_softwareSharing"
            $idParts = explode('_', $externalIdRaw);
            $identifier = $idParts[0]; 
            $subscriptionType = $idParts[1] ?? 'internetCustomer'; // Default logic if no suffix

            if ($subscriptionType === 'internetCustomer') {
                return $this->processInternetCustomerWebhook($identifier, $data, $user, $request);
            } elseif ($subscriptionType === 'softwareSharing') {
                return $this->processSoftwareSharingWebhook($identifier, $data, $user, $request);
            } else {
                Log::error('Unknown subscription type for Xendit callback', [
                    'external_id' => $externalIdRaw,
                ]);
                return response()->json(['error' => 'Unknown subscription type'], 400);
            }

        } catch (\Exception $e) {
            $this->logging($request, 500, $e);

            Log::error('Xendit webhook processing failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Process Internet Customer Webhook Logic
     */
    private function processInternetCustomerWebhook($purchaseId, $data, $user, $request)
    {
        $purchase = InternetCustomerPurchase::where('id', $purchaseId)->first();
            
        if (!$purchase) {
            Log::error('Internet Purchase not found for Xendit callback', [
                'purchase_id' => $purchaseId,
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
            Log::warning('Invalid Xendit callback token format (Internet Customer)', [
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

                // Smart billing date calculation
                $periodStartDate = Carbon::parse($purchase->period_start);
                $maxBillingDate = config('services.internet_custom.max_billing_date', 20);
                
                $currentBillingDay = $periodStartDate->day;
                
                if ($currentBillingDay > $maxBillingDate) {
                    $startBillingDate = $periodStartDate->copy()->addMonths($purchase->payment_months)->firstOfMonth();
                } else {
                    $startBillingDate = $periodStartDate->copy()->addMonths($purchase->payment_months);
                }
                
                $gracePeriod = config('services.internet_custom.end_billing_of_days', 5);
                $endBillingDate = $startBillingDate->copy()->addDays($gracePeriod);

                $customerInternet->update([
                    'start_billing_date' => $startBillingDate->format('Y-m-d'),
                    'end_billing_date' => $endBillingDate->format('Y-m-d')
                ]);

                GenerateInternetPurchaseCouponJob::dispatch($internetCustomer->id, $purchase->id, $purchase->payment_months);

                Log::info('Payment confirmed for Internet purchase', [
                    'company_id' => $internetCustomer->company_id,
                    'purchase_id' => $purchase->id
                ]);

                $this->afterPayment($purchase, $internetCustomer);

                SendPaymentSuccessWaJob::dispatch($purchase->id);

                $urlResutl = route('internet-customer.customer.show', [
                    'code' => $internetCustomer->code,
                    'status' => 'success',
                    'purchase' => $purchase->id
                ]);

                break;

            case 'expired':
            case 'failed':
                $purchase->update([
                    'confirmation_finance_at' => null,
                    'user_finance_id' => null,
                    'period_start' => null,
                    'period_end' => null,
                    'payment_method' => null,
                    'xendit_response' => json_encode($data),
                ]);
                Log::info('Invoice failed/expired for Internet purchase', [
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
    }

    /**
     * Process Software Sharing Webhook Logic
     */
    private function processSoftwareSharingWebhook($orderNumber, $data, $user, $request)
    {
        // For software sharing, the identifier is the order_number ending in _softwareSharing
        $subscription = CustomerSubscription::where('order_number', $orderNumber)->first();

        if (!$subscription) {
            Log::error('Subscription not found for Xendit callback', [
                'order_number' => $orderNumber,
            ]);
            return response()->json(['error' => 'Subscription not found'], 200);
        }

        // Initialize Subscription Xendit service
        $xenditService = new \App\Services\SubscriptionXenditService($subscription->company_id);
        
        // Verify callback token
        $callbackToken = $request->header('x-api-key') ?? $request->header('x-callback-token');
        
        if (!$xenditService->verifyCallbackToken($callbackToken)) {
            Log::warning('Invalid Xendit callback token (Software Sharing)', [
                'company_id' => $subscription->company_id
            ]);
        }

        $status = $data['status'] ?? '';
        $urlResutl = '';

        switch ($status) {
            case 'paid':
                // Find latest pending payment
                $payment = SubscriptionPayment::where('subscription_id', $subscription->id)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();

                if ($payment) {
                    $payment->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'payment_proof' => 'AUTO_VERIFIED', // System verified
                        'notes' => 'Otomatis via webhook Xendit/KeloolaPay',
                        'raw_response' => json_encode($data),
                        'finance_user_id' => $user->id, // System auto-confirm
                        'finance_user_at' => now(),
                    ]);
                }

                // Update subscription status and dates
                $package = $subscription->package;

                $tanggalMulai = $subscription->tanggal_expired && $subscription->tanggal_expired->isFuture()
                                ? $subscription->tanggal_expired
                                : now();

                $tanggalExpired = SubscriptionService::calculateExpiredDate($tanggalMulai, $package->durasi_hari ?? null);

                $subscription->update([
                    'status' => 'active',
                    'payment_status' => 'paid',
                    'tanggal_mulai' => $subscription->tanggal_mulai ?? $tanggalMulai, // keep original start date; $tanggalMulai used for new/renewal
                    'tanggal_expired' => $tanggalExpired,
                ]);

                Log::info('Payment confirmed for Software Subscription', [
                    'company_id' => $subscription->company_id,
                    'subscription_id' => $subscription->id,
                    'order_number' => $orderNumber
                ]);

                $urlResutl = route('customer-checkout.payment.success', ['order' => $orderNumber]);
                $this->notifyTeamSuccess($subscription, $user); 
                break;

            case 'expired':
            case 'failed':
                $payment = SubscriptionPayment::where('subscription_id', $subscription->id)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();

                if ($payment) {
                    $payment->update([
                        'status' => 'failed',
                        'raw_response' => json_encode($data),
                    ]);
                }

                Log::info('Invoice failed/expired for Software Subscription', [
                    'company_id' => $subscription->company_id,
                    'subscription_id' => $subscription->id
                ]);

                $urlResutl = route('customer-checkout.payment.failed', ['order' => $orderNumber]);
                break;

            default:
                Log::info('Unhandled Xendit status for Software Subscription', [
                    'status' => $status,
                    'data' => $data
                ]);
                $urlResutl = route('customer-checkout.payment.pending', ['order' => $orderNumber]);
                break;
        }

        $this->logging($request, 200);
        return response()->json(['success' => true, 'redirect_url' => $urlResutl]);
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
        if (!$internetCustomers->installation) {
            $post['status'] = ParamSchema::PROCESS_INSTALLATION;
            $internetPurchase->customer->update($post);

            $message = "🔧 *Notifikasi Pemasangan*\n\n"
                . "Pelanggan dengan kode *{$internetPurchase->customer->code}* telah berhasil melakukan pembayaran dan siap untuk proses pemasangan.\n\n"
                . "Mohon segera dijadwalkan untuk instalasi. Terima kasih. 🙏";

            $this->sentWaToOffice($internetPurchase->customer->company_id, $message);
        } else {
            $post['status'] = ParamSchema::REACTIVATED;
            $internetPurchase->customer->update($post);

            dispatch(new ProvisionCustomerJob($internetCustomers->id));
            \App\Jobs\SyncInstalledCustomersJob::dispatch([$internetCustomers->id]);
        }
    }

    private function sentInbox($to,$from, $message,$directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent($to, $from, $message, $directUrl);
        return true;
    }

    private function sentWaToOffice(int $companyId, string $message): void
    {
        try {
            $settings = SettingCompany::byCompany($companyId)->get()->pluck('field_value', 'field_title');

            $officePhone = $settings['internet_phone'] ?? null;
            if (!$officePhone) return;

            $serverWablas = $settings['server_wablas'] ?? null;
            $tokenWablas  = $settings['token_wablas'] ?? null;
            if (!$serverWablas || !$tokenWablas) return;

            $client = new WablasClient($serverWablas, $tokenWablas, $settings['webhook_key_wablas'] ?? null);
            (new WablasMessage($client))->single_text($officePhone, $message);
        } catch (\Throwable $e) {
            Log::warning('[XenditController::sentWaToOffice] Gagal kirim WA ke kantor: ' . $e->getMessage());
        }
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
}