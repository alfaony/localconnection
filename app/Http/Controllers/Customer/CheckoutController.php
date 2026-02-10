<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Software;
use App\Models\SoftwarePackage;
use App\Models\CustomerSubscription;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
use App\Services\SubscriptionXenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected $subscriptionService;

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

        // Check if slots available
        $hasAvailableSlots = $software->availableMasterAccounts->isNotEmpty();

        if (!$hasAvailableSlots) {
            return redirect()
                ->route('customer.software.show', $slug)
                ->with('error', 'Maaf, slot untuk software ini sudah penuh. Silakan hubungi admin atau coba lagi nanti.');
        }

        return view('customer.checkout.show', compact('software', 'package'));
    }

    /**
     * Process checkout
     */
    public function process(Request $request, $slug, $packageId)
    {
        $validated = $request->validate([
            'agree_terms' => 'required|accepted',
        ]);

        $user = Auth::user();

        // Get software and package
        $software = Software::where('slug', $slug)->active()->firstOrFail();
        $package = SoftwarePackage::findOrFail($packageId);

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

            // Create payment record
            $payment = SubscriptionPayment::create([
                'company_id' => $software->company_id,
                'software_id' => $software->id,
                'subscription_id' => $subscription->id,
                'amount' => $package->harga,
                'xendit_external_id' => $subscription->order_number,
                'status' => 'pending',
                'expired_at' => now()->addHours(24),
            ]);

            // Create Xendit invoice
            $xenditService = new SubscriptionXenditService($software->company_id);
            
            if (!$xenditService->isActive()) {
                throw new \Exception('Payment gateway is not configured for this service');
            }

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

            Log::info('Checkout processed successfully', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'order_number' => $subscription->order_number,
                'invoice_id' => $invoice['id'],
            ]);

            // Redirect to Xendit payment page
            return redirect($invoice['invoice_url']);

        } catch (\Exception $e) {
            DB::rollBack();

            dd($e);
            Log::error('Checkout failed', [
                'user_id' => $user->id,
                'software_slug' => $slug,
                'package_id' => $packageId,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('customer.software.show', $slug)
                ->with('error', 'Checkout gagal: ' . $e->getMessage());
        }
    }
}