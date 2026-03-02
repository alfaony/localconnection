<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Models\SettingCompany;
use App\Models\SoftwarePackage;
use App\Models\SubscriptionPayment;
use App\Services\MidtransService;
use App\Services\SubscriptionPaymentService;
use App\Services\SubscriptionService;
use App\Services\SubscriptionXenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display customer's subscriptions dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = CustomerSubscription::byUser($user->id)
            ->with(['masterAccount.software', 'package', 'latestPayment', 'company']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->latest()->paginate(10);

        return view('customer.subscriptions.index', compact('subscriptions'));
    }

    /**
     * Display the specified subscription detail
     */
    public function show(CustomerSubscription $subscription)
    {
        // Check if user owns this subscription
        if ($subscription->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        $subscription->load([
            'masterAccount.software',
            'package',
            'payments' => function($query) {
                $query->latest();
            },
            'company'
        ]);

        // Only show credentials if subscription is active and paid
        $showCredentials = $subscription->isActivePaid();

        return view('customer.subscriptions.show', compact('subscription', 'showCredentials'));
    }

    /**
     * Show renewal form
     */
    public function renew(CustomerSubscription $subscription)
    {
        // Check if user owns this subscription
        if ($subscription->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        $subscription->load(['masterAccount.software', 'software', 'package']);

        // Get available packages for renewal (from the subscription's software)
        $softwareId = $subscription->software_id ?? $subscription->masterAccount->software_id ?? null;
        $packages = SoftwarePackage::where('software_id', $softwareId)
            ->active()
            ->get();

        // Get payment methods and PPN settings — exactly same as CustomerCheckoutController
        $paymentService = new SubscriptionPaymentService($subscription->company_id);
        $paymentMethods = $paymentService->getAvailablePaymentMethods();

        $settingCompany = SettingCompany::byCompany($subscription->company_id)
            ->get()->pluck('field_value', 'field_title');

        $ppnSettings = [
            'rate'     => floatval($settingCompany['ppn_default_software_sharing'] ?? 0),
            'manual'   => true, // Manual always applies PPN
            'xendit'   => ($settingCompany['xendit_pay_with_ppn_software_subscription'] ?? '0') === '1',
            'midtrans' => ($settingCompany['midtrans_pay_with_ppn_software_sharing']   ?? '0') === '1',
        ];

        // Check for existing pending payments
        $pendingPayment = $subscription->payments()->whereIn('status', ['pending', 'unpaid'])->latest()->first();

        // Check slot availability (if subscription expired, or masterAccount is inactive/full)
        $hasFreeSlot = true;
        $masterAccount = $subscription->masterAccount;
        if ($subscription->status === 'expired' || !$masterAccount || $masterAccount->status !== 'active') {
            if (!$masterAccount || !$masterAccount->hasSlotsAvailable()) {
                $hasFreeSlot = $this->subscriptionService->checkSlotsAvailability(
                    $softwareId,
                    $subscription->company_id
                );
            }
        }

        return view('customer.subscriptions.renew', compact('subscription', 'packages', 'paymentMethods', 'ppnSettings', 'pendingPayment', 'hasFreeSlot'));
    }

    /**
     * Process renewal
     */
    public function processRenewal(Request $request, CustomerSubscription $subscription)
    {
        // Check if user owns this subscription
        if ($subscription->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'package_id'       => 'required|exists:software_packages,id',
            'payment_gateway'  => 'required|string|in:manual,xendit,midtrans',
            'selected_bank'    => 'nullable|integer',
        ]);

        
        $user = Auth::user();
        $package = SoftwarePackage::findOrFail($validated['package_id']);

        if($subscription->id != $validated['package_id']){
            $subscription->update([
                'package_id' => $validated['package_id'],
            ]);
        }

        // Guard: Limit percobaan renewal (maksimal 2x retry = 3 payment) dalam 1 jam terakhir
        $recentPaymentsCount = $subscription->payments()
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentPaymentsCount >= 3) {
            return redirect()
                ->route('customer-subscription.show', $subscription->id)
                ->with('error', 'Anda telah mencapai batas maksimal pembuatan pesanan perpanjangan (2 kali). Silakan tunggu 1 jam lagi sebelum mencoba kembali.');
        }

        DB::beginTransaction();

        try {
            // Clean up any existing pending payments to avoid slot leaks
            $pendingPayments = $subscription->payments()->whereIn('status', ['pending', 'unpaid'])->get();
            foreach ($pendingPayments as $p) {
                $p->update(['status' => 'expired', 'expired_at' => now()]);
                // If subscription is already expired, it means this pending payment held a temporary slot. Release it.
                if ($subscription->status === 'expired' && $subscription->masterAccount) {
                    $subscription->masterAccount->releaseSlot();
                }
            }

            // If subscription is expired or master account is inactive, must re-check slot availability
            // (slot was released when it expired — cannot assume it's still available)
            if ($subscription->status === 'expired' || !$subscription->masterAccount || $subscription->masterAccount->status !== 'active') {
                // Refresh master account in case it changed
                $subscription->load('masterAccount');
                $masterAccount = $subscription->masterAccount;

                if (!$masterAccount || !$masterAccount->hasSlotsAvailable()) {
                    // Original master account gone or full — find a new one
                    $hasFreeSlot = $this->subscriptionService->checkSlotsAvailability(
                        $subscription->software_id,
                        $subscription->company_id
                    );

                    if (!$hasFreeSlot) {
                        throw new \Exception('Maaf, slot sudah penuh. Silakan hubungi admin.');
                    }

                    $masterAccount = $this->subscriptionService->findAvailableMasterAccount(
                        $subscription->software_id,
                        $subscription->company_id
                    );

                    $subscription->update(['master_account_id' => $masterAccount->id]);
                }

                // Re-reserve slot (it was released on expire)
                $masterAccount->reserveSlot();
            }


            // Create new payment record and handle gateway routing
            $gateway = $validated['payment_gateway'];
            $paymentService = new SubscriptionPaymentService($subscription->company_id);

            if ($gateway === 'manual') {
                // Use processManualTransfer to properly save gateway, bank info, and PPN
                $selectedBank = $validated['selected_bank'] ?? 0;
                $paymentResult = $paymentService->processManualTransfer($subscription, $package, $selectedBank);

                if (!$paymentResult['success']) {
                    throw new \Exception($paymentResult['message']);
                }

                DB::commit();

                return redirect()
                    ->route('customer-checkout.payment.pending', ['order' => $subscription->order_number])
                    ->with('success', 'Pesanan perpanjangan berhasil dibuat! Silakan transfer ke rekening yang tersedia, lalu upload bukti pembayaran.');

            } elseif ($gateway === 'xendit') {
                $ppnCalc = $paymentService->calculatePpn($package->harga, $gateway);

                $payment = SubscriptionPayment::create([
                    'company_id'         => $subscription->company_id,
                    'subscription_id'    => $subscription->id,
                    'amount'             => $ppnCalc['total'],
                    'subtotal'           => $ppnCalc['subtotal'],
                    'ppn_rate'           => $ppnCalc['ppn_rate'],
                    'ppn_amount'         => $ppnCalc['ppn_amount'],
                    'payment_gateway'    => 'xendit',
                    'xendit_external_id' => $subscription->order_number . '-RNW-' . time(),
                    'status'             => 'pending',
                    'expired_at'         => now()->addHours(24),
                ]);

                $xenditService = new SubscriptionXenditService($subscription->company_id);
                $invoiceResult = $xenditService->createInvoice($subscription, $package, $user);

                if (!$invoiceResult['success']) {
                    throw new \Exception($invoiceResult['message']);
                }

                $invoice = $invoiceResult['invoice'];
                $payment->update(['xendit_invoice_id' => $invoice['id'], 'payment_channel' => $invoice['payment_url']]);

                DB::commit();
                return redirect($invoiceResult['payment_url']);

            } elseif ($gateway === 'midtrans') {
                $ppnCalc = $paymentService->calculatePpn($package->harga, $gateway);

                $payment = SubscriptionPayment::create([
                    'company_id'         => $subscription->company_id,
                    'subscription_id'    => $subscription->id,
                    'amount'             => $ppnCalc['total'],
                    'subtotal'           => $ppnCalc['subtotal'],
                    'ppn_rate'           => $ppnCalc['ppn_rate'],
                    'ppn_amount'         => $ppnCalc['ppn_amount'],
                    'payment_gateway'    => 'midtrans',
                    'xendit_external_id' => $subscription->order_number . '-RNW-' . time(),
                    'status'             => 'pending',
                    'expired_at'         => now()->addHours(24),
                ]);

                $midtransService = new MidtransService($subscription->company_id);
                $result = $midtransService->createTransactionForSubscription($subscription, $package, $user, $payment);

                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }

                $payment->update(['xendit_invoice_id' => $result['order_id'] ?? null, 'payment_channel' => $result['redirect_url']]);

                DB::commit();
                return redirect($result['redirect_url']);

            } else {
                throw new \Exception('Metode pembayaran tidak valid.');
            }

        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Renewal gagal: ' . $e->getMessage());
        }
    }

    /**
     * Display payment history
     */
    public function payments(CustomerSubscription $subscription)
    {
        // Check if user owns this subscription
        if ($subscription->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        $payments = $subscription->payments()
            ->latest()
            ->paginate(15);

        return view('customer.subscriptions.payments', compact('subscription', 'payments'));
    }

    /**
     * Cancel pending renewal payment
     */
    public function cancelRenewalPayment(Request $request, CustomerSubscription $subscription)
    {
        // Check if user owns this subscription
        if ($subscription->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        $pendingPayments = $subscription->payments()->whereIn('status', ['pending', 'unpaid'])->get();
        if ($pendingPayments->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada pembayaran yang tertunda.');
        }

        foreach ($pendingPayments as $p) {
            $p->update(['status' => 'expired', 'expired_at' => now()]);
            // Re-release slot if subscription is already expired
            if ($subscription->status === 'expired' && $subscription->masterAccount) {
                $subscription->masterAccount->releaseSlot();
            }
        }

        return redirect()->route('customer-subscription.renew', $subscription->id)->with('success', 'Order renewal sebelumnya berhasil dibatalkan. Silakan buat pesanan baru.');
    }

    /**
     * Resume pending renewal payment
     */
    public function resumeRenewalPayment(Request $request, CustomerSubscription $subscription)
    {
        // Check if user owns this subscription
        if ($subscription->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        $payment = $subscription->payments()->whereIn('status', ['pending', 'unpaid'])->latest()->first();

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
        // dd("here");
        // dd($payment->payment_channel);
        $payment->update(['status' => 'expired', 'expired_at' => now()]);

        // Selalu redirect ke retry payment di checkout untuk membuat payment url baru
        return redirect()
            ->route('customer-checkout.retry-payment', [
                'subscription' => $subscription->id,
                'gateway'      => $payment->payment_gateway,
            ]);
    }
}