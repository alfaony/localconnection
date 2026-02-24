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

        return view('customer.subscriptions.renew', compact('subscription', 'packages', 'paymentMethods', 'ppnSettings'));
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

        DB::beginTransaction();

        try {
            // If subscription is expired, reactivate using the SAME master account
            // (slot was already theirs — no need to find a new slot)
            if ($subscription->status === 'expired') {
                $masterAccount = $subscription->masterAccount;

                if (!$masterAccount) {
                    // Master account has been removed; find a new available slot
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
                    $masterAccount->reserveSlot();

                    $subscription->update(['master_account_id' => $masterAccount->id]);
                }
                // Else: keep using the same master account — nothing to change
            }


            // Create new payment record and handle gateway routing
            $gateway = $validated['payment_gateway'];
            $paymentService = new SubscriptionPaymentService($subscription->company_id);
            $ppnCalc = $paymentService->calculatePpn($package->harga, $gateway);

            $payment = SubscriptionPayment::create([
                'company_id'         => $subscription->company_id,
                'subscription_id'    => $subscription->id,
                'amount'             => $ppnCalc['total'],
                'xendit_external_id' => $subscription->order_number . '-RNW-' . time(),
                'status'             => 'pending',
                'expired_at'         => now()->addHours(24),
            ]);

            if ($gateway === 'xendit') {
                $xenditService = new SubscriptionXenditService($subscription->company_id);
                $invoiceResult = $xenditService->createInvoice($subscription, $package, $user);

                if (!$invoiceResult['success']) {
                    throw new \Exception($invoiceResult['message']);
                }

                $invoice = $invoiceResult['invoice'];
                $payment->update(['xendit_invoice_id' => $invoice['id']]);

                DB::commit();
                return redirect($invoiceResult['payment_url']);

            } elseif ($gateway === 'midtrans') {
                $midtransService = new MidtransService($subscription->company_id);
                $result = $midtransService->createTransactionForSubscription($subscription, $package, $user, $payment);

                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }

                $payment->update(['xendit_invoice_id' => $result['order_id'] ?? null]);

                DB::commit();
                return redirect($result['redirect_url']);

            } else {
                // Manual transfer
                $payment->update(['status' => 'pending']);

                DB::commit();

                return redirect()
                    ->route('customer-subscription.show', $subscription->id)
                    ->with('success', 'Pesanan perpanjangan berhasil dibuat! Silakan transfer ke rekening yang tersedia, lalu upload bukti pembayaran.');
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
}