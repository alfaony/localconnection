<?php
namespace App\Http\Controllers\Customer;


use App\Http\Controllers\Controller;
use App\Models\Software;
use App\Models\SoftwarePackage;
use App\Models\CustomerSubscription;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
use App\Services\SubscriptionPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerCheckoutController extends Controller
{
    protected $subscriptionService;
    protected $paymentService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Show checkout page
     */
    public function show($slug, $packageId)
    {
        $software = Software::where('slug', $slug)
            ->with(['company', 'availableMasterAccounts'])
            ->active()
            ->firstOrFail();

        $package = SoftwarePackage::where('id', $packageId)
            ->where('software_id', $software->id)
            ->active()
            ->firstOrFail();

        // ── Detect stale pending subscription ───────────────────────────
        $pendingSubscription = $this->subscriptionService->findPendingSubscription(
            Auth::id(), $software->id
        );
        $pendingPayment = $pendingSubscription
            ? $pendingSubscription->payments->first()
            : null;
        // ────────────────────────────────────────────────────────────────

        // ── Detect existing active/expired subscription to prevent duplicate ──
        $existingSubscription = CustomerSubscription::where('user_id', Auth::id())
            ->where('software_id', $software->id)
            ->whereIn('status', ['expired'])
            ->latest()
            ->first();

        if ($existingSubscription) {
            $msg = $existingSubscription->status === 'active' 
                ? 'Anda sudah memiliki langganan aktif untuk software ini. Silakan lakukan perpanjangan dari sini.' 
                : 'Anda memiliki riwayat langganan untuk software ini. Silakan lanjutkan dengan memperpanjang langganan tersebut.';
            return redirect()->route('customer-subscription.renew', [
                'subscription' => $existingSubscription->id,
                'selected_package' => $packageId
            ])->with('info', $msg);
        }
        // ────────────────────────────────────────────────────────────────

        // Check if slots available
        $hasAvailableSlots = $software->availableMasterAccounts->isNotEmpty();

        if (!$hasAvailableSlots && !$pendingSubscription) {
            return redirect()
                ->route('customer-software.show', $slug)
                ->with('error', 'Maaf, slot untuk software ini sudah penuh. Silakan hubungi admin atau coba lagi nanti.');
        }

        // Get available payment methods
        $this->paymentService = new SubscriptionPaymentService($software->company_id);
        $paymentMethods = $this->paymentService->getAvailablePaymentMethods();
        
        if (empty($paymentMethods)) {
            return redirect()
                ->route('customer-software.show', $slug)
                ->with('error', 'Tidak ada metode pembayaran yang tersedia. Silakan hubungi admin.');
        }

        // Calculate PPN base metrics
        $packagePrice = $package->harga;
        
        $settingCompany = \App\Models\SettingCompany::byCompany($software->company_id)
            ->get()
            ->pluck('field_value', 'field_title');

        // Extract PPn settings specifically for frontend logic
        $ppnSettings = [
            'rate' => floatval($settingCompany['ppn_default_software_sharing'] ?? 0),
            'manual' => true, // Manual always applies based on previous assumption
            'xendit' => ($settingCompany['xendit_pay_with_ppn_software_software_subscription'] ?? '0') === '1',
            'midtrans' => ($settingCompany['midtrans_pay_with_ppn_software_software_subscription'] ?? '0') === '1',
        ];

        return view('customer.checkout.show', compact(
            'software', 'package', 'paymentMethods', 'packagePrice',
            'ppnSettings', 'settingCompany',
            'pendingSubscription', 'pendingPayment'
        ));
    }

    /**
     * Process checkout
     */
    public function process(Request $request, $slug, $packageId)
    {
        $validated = $request->validate([
            'agree_terms' => 'required|accepted',
            'payment_gateway' => 'required|in:manual,xendit,midtrans',
            'selected_bank' => 'required_if:payment_gateway,manual|integer',
        ]);

        $user = Auth::user();

        // Get software and package
        $software = Software::where('slug', $slug)->active()->firstOrFail();
        $package = SoftwarePackage::findOrFail($packageId);

        // ── Guard: cek apakah sudah ada pending subscription ────────────
        $existingPending = $this->subscriptionService->findPendingSubscription($user->id, $software->id);
        if ($existingPending) {
            return redirect()
                ->route('customer-checkout.show', [$slug, $packageId])
                ->with('error', '⚠️ Anda masih memiliki pembayaran yang belum selesai untuk software ini (Order #' 
                    . $existingPending->order_number 
                    . '). Silakan selesaikan atau batalkan terlebih dahulu.');
        }
        // ────────────────────────────────────────────────────────────────

        // ── Detect existing active/expired subscription to prevent duplicate ──
        $existingSubscription = CustomerSubscription::where('user_id', Auth::id())
            ->where('software_id', $software->id)
            ->whereIn('status', ['active', 'expired'])
            ->latest()
            ->first();

        if ($existingSubscription) {
            $msg = $existingSubscription->status === 'active' 
                ? 'Anda sudah memiliki langganan aktif. Silakan lakukan perpanjangan.' 
                : 'Anda sudah memiliki riwayat langganan. Silakan lanjutkan dengan perpanjangan.';
            return redirect()->route('customer-subscription.renew', [
                'subscription' => $existingSubscription->id,
                'selected_package' => $packageId
            ])->with('info', $msg);
        }
        // ────────────────────────────────────────────────────────────────

        // Initialize payment service
        $this->paymentService = new SubscriptionPaymentService($software->company_id);

        // Verify payment method is available
        $availableMethods = $this->paymentService->getAvailablePaymentMethods();
        if (!isset($availableMethods[$validated['payment_gateway']])) {
            return redirect()
                ->route('customer-software.show', $slug)
                ->with('error', 'Metode pembayaran yang dipilih tidak tersedia.');
        }

        DB::beginTransaction();

        try {
            // Create subscription
            $result = $this->subscriptionService->createSubscription([
                'company_id' => $software->company_id,
                'user_id' => $user->id,
                'software_id' => $software->id,
                'package_id' => $package->id,
                'harga_bayar' => $package->harga,
            ]);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $subscription = $result['subscription'];

            // Process payment based on selected gateway
            $paymentGateway = $validated['payment_gateway'];
            
            switch ($paymentGateway) {
                case 'manual':
                    $paymentResult = $this->paymentService->processManualTransfer(
                        $subscription,
                        $package,
                        $validated['selected_bank']
                    );
                    
                    break;

                case 'xendit':
                    $paymentResult = $this->paymentService->processXenditPayment(
                        $subscription,
                        $package,
                        $user
                    );

                    break;

                case 'midtrans':
                    $paymentResult = $this->paymentService->processMidtransPayment(
                        $subscription,
                        $package,
                        $user
                    );
                    break;

                default:
                    throw new \Exception('Invalid payment gateway');
            }

            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['message']);
            }

            DB::commit();

            Log::info('Checkout processed successfully', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'order_number' => $subscription->order_number,
                'payment_gateway' => $paymentGateway,
            ]);

            // Redirect based on payment gateway
            return $this->redirectAfterPayment($paymentGateway, $paymentResult, $subscription);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Checkout failed', [
                'user_id' => $user->id,
                'software_slug' => $slug,
                'package_id' => $packageId,
                'payment_gateway' => $validated['payment_gateway'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('customer-software.show', $slug)
                ->with('error', 'Checkout gagal: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a stuck/pending subscription and release slot
     */
    public function cancelPending(Request $request, $subscriptionId)
    {
        $user = Auth::user();

        $subscription = CustomerSubscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->firstOrFail();

        $result = $this->subscriptionService->cancelSubscription(
            $subscription,
            'Dibatalkan oleh user melalui halaman checkout'
        );

        if (!$result['success']) {
            return redirect()->back()->with('error', 'Gagal membatalkan pesanan: ' . ($result['message'] ?? ''));
        }

        // Determine where to go back
        $slug = $subscription->masterAccount->software->slug
            ?? $request->query('redirect_slug', null);

        $redirectTo = $slug
            ? route('customer-software.show', $slug)
            : route('customer-software.index');

        return redirect($redirectTo)
            ->with('success', '✅ Pesanan berhasil dibatalkan dan slot sudah dibebaskan. Anda bisa melakukan pemesanan baru.');
    }

    /**
     * Resume an existing pending payment (redirect back to gateway URL)
     */
    public function resumePayment(Request $request, $subscriptionId)
    {
        $user = Auth::user();

        $subscription = CustomerSubscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->with(['payments' => fn($q) => $q->whereIn('status', ['pending', 'unpaid'])->latest()])
            ->firstOrFail();

        // Guard: auto-expire if slot reservation deadline has passed
        if ($subscription->isSlotExpired()) {
            $this->subscriptionService->cancelSubscription($subscription, 'Slot reservation expired');
            return redirect()
                ->route('customer-software.index')
                ->with('error', '⏰ Waktu reservasi slot sudah habis. Silakan lakukan pemesanan baru.');
        }

        $payment = $subscription->payments->first();

        if (!$payment) {
            return redirect()->back()->with('error', 'Tidak ada data pembayaran yang ditemukan.');
        }

        // For manual transfer → go to pending page
        if ($payment->payment_gateway === 'manual') {
            return redirect()->route('customer-checkout.payment.pending', $subscription->order_number);
        }

         // Coba redirect ke URL payment yang sudah tersimpan
        if ($payment->payment_channel) {
            return redirect($payment->payment_channel);
        }

        // Batalkan pembayaran lama
        $payment->update(['status' => 'expired', 'expired_at' => now()]);

        // URL expired → otomatis redirect ke retry (buat ulang tanpa konfirmasi tambahan)
        return redirect()
            ->route('customer-checkout.retry-payment', [
                'subscription' => $subscriptionId,
                'gateway'      => $payment->payment_gateway,
            ]);
    }

    /**
     * Retry payment: void lama, buat fresh gateway transaction untuk existing subscription.
     * Slot TIDAK di-release — subscription tetap, hanya payment baru dibuat.
     */
    public function retryPayment(Request $request, $subscriptionId)
    {
        $user = Auth::user();

        $subscription = CustomerSubscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->with(['package', 'masterAccount.software'])
            ->firstOrFail();

        // Determine if this is a renewal (status active/expired, or has previously paid payments)
        $isRenewal = in_array($subscription->status, ['active', 'expired']) 
                  || $subscription->payments()->where('status', 'paid')->exists();

        // Guard: auto-expire if slot reservation deadline has passed
        // ONLY FOR INITIAL CHECKOUT! Renewals already hold a valid slot/status.
        if (!$isRenewal && $subscription->isSlotExpired()) {
            $this->subscriptionService->cancelSubscription($subscription, 'Slot reservation expired');
            return redirect()
                ->route('customer-software.index')
                ->with('error', '⏰ Waktu reservasi slot sudah habis. Silakan lakukan pemesanan baru.');
        }

        $package = $subscription->package;
        if (!$package) {
            return redirect()->back()->with('error', 'Paket tidak ditemukan.');
        }

        // Tentukan gateway — dari query param atau dari payment lama
        $gateway = $request->query('gateway');
        if (!$gateway) {
            $oldPayment = $subscription->payments()
                ->whereIn('status', ['pending', 'unpaid', 'expired'])
                ->latest()->first();
            $gateway = $oldPayment->payment_gateway ?? 'manual';
        }

        $this->paymentService = new SubscriptionPaymentService($subscription->company_id);

        // Guard: Limit percobaan retry (maksimal 2x retry = 3 payment) dalam 1 jam terakhir
        $recentPaymentsCount = $subscription->payments()
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentPaymentsCount >= 3) {
            return redirect()
                ->route('customer-software.index')
                ->with('error', 'Anda telah mencapai batas maksimal percobaan pembayaran (2 kali). Silakan tunggu 1 jam lagi sebelum mencoba kembali.');
        }

        DB::beginTransaction();
        try {
            $result = $this->paymentService->retryGatewayPayment($subscription, $package, $user, $gateway);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            DB::commit();

            Log::info('Payment retried successfully', [
                'user_id'         => $user->id,
                'subscription_id' => $subscription->id,
                'order_number'    => $subscription->order_number,
                'gateway'         => $gateway,
            ]);

            return $this->redirectAfterPayment($gateway, $result, $subscription);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Retry payment failed', [
                'user_id'         => $user->id,
                'subscription_id' => $subscriptionId,
                'gateway'         => $gateway,
                'error'           => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal membuat ulang pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Redirect after payment based on gateway
     */
    protected function redirectAfterPayment($gateway, $paymentResult, $subscription)
    {
        switch ($gateway) {
            case 'manual':
                // Redirect to payment pending page
                return redirect()
                    ->route('customer-checkout.payment.pending', ['order' => $subscription->order_number])
                    ->with('success', 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.');

            case 'xendit':
                // Redirect to Keloola Pay payment page
                $paymentUrl = $paymentResult['invoice']['payment_url'] ?? null;
                if (!$paymentUrl) {
                    throw new \Exception('Payment URL not found');
                }


                return redirect($paymentUrl);

            case 'midtrans':
                // Redirect to Midtrans SNAP page
                return redirect($paymentResult['transaction']['redirect_url']);

            default:
                throw new \Exception('Invalid payment gateway');
        }
    }
    /**
     * Show payment pending page (for manual transfer)
     */
    public function paymentPending($order)
    {
        $subscription = CustomerSubscription::where('order_number', $order)
            ->with(['package', 'masterAccount.software', 'payments' => function($query) {
                $query->latest();
            }])
            ->firstOrFail();

        // Get latest payment
        $payment = $subscription->payments->first();

        if (!$payment || !$payment->isManualTransfer()) {
            return redirect()->route('customer-software.index')
                ->with('error', 'Payment not found or invalid payment method');
        }

        return view('customer.payment.pending', compact('subscription', 'payment'));
    }

    /**
     * Show payment success page
     */
    public function paymentSuccess($order)
    {
        $subscription = CustomerSubscription::where('order_number', $order)
            ->with(['package', 'masterAccount.software'])
            ->firstOrFail();

        return view('customer.payment.success', compact('subscription'));
    }

    /**
     * Show payment failed page
     */
    public function paymentFailed($order)
    {
        $subscription = CustomerSubscription::where('order_number', $order)
            ->with(['package', 'masterAccount.software'])
            ->firstOrFail();

        return view('customer.payment.failed', compact('subscription'));
    }
}

