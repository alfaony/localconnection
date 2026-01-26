<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Models\SoftwarePackage;
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

        $subscription->load(['masterAccount.software', 'package']);

        // Get available packages for renewal
        $packages = SoftwarePackage::where('software_id', $subscription->masterAccount->software_id)
            ->active()
            ->get();

        return view('customer.subscriptions.renew', compact('subscription', 'packages'));
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
            'package_id' => 'required|exists:software_packages,id',
        ]);

        $user = Auth::user();
        $package = SoftwarePackage::findOrFail($validated['package_id']);

        DB::beginTransaction();

        try {
            // If subscription is expired, need to check slot availability
            if ($subscription->status === 'expired') {
                $hasSlot = $this->subscriptionService->checkSlotsAvailability(
                    $subscription->masterAccount->software_id,
                    $subscription->company_id
                );

                if (!$hasSlot) {
                    throw new \Exception('Maaf, slot sudah penuh. Silakan hubungi admin.');
                }

                // Reserve slot for renewed subscription
                $masterAccount = $this->subscriptionService->findAvailableMasterAccount(
                    $subscription->masterAccount->software_id,
                    $subscription->company_id
                );
                $masterAccount->reserveSlot();

                // Update subscription with new master account if needed
                $subscription->update([
                    'master_account_id' => $masterAccount->id,
                ]);
            }

            // Create new payment record
            $payment = SubscriptionPayment::create([
                'company_id' => $subscription->company_id,
                'subscription_id' => $subscription->id,
                'amount' => $package->harga,
                'xendit_external_id' => $subscription->order_number . '-RNW-' . time(),
                'status' => 'pending',
                'expired_at' => now()->addHours(24),
            ]);

            // Create Xendit invoice
            $xenditService = new SubscriptionXenditService($subscription->company_id);
            $invoiceResult = $xenditService->createInvoice($subscription, $package, $user);

            if (!$invoiceResult['success']) {
                throw new \Exception($invoiceResult['message']);
            }

            $invoice = $invoiceResult['invoice'];

            // Update payment with Xendit invoice ID
            $payment->update([
                'xendit_invoice_id' => $invoice['id'],
            ]);

            DB::commit();

            // Redirect to Xendit payment page
            return redirect($invoice['invoice_url']);

        } catch (\Exception $e) {
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