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

class CheckoutController extends Controller
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

        // Check if slots available
        $hasAvailableSlots = $software->availableMasterAccounts->isNotEmpty();

        if (!$hasAvailableSlots) {
            return redirect()
                ->route('customer.software.show', $slug)
                ->with('error', 'Maaf, slot untuk software ini sudah penuh. Silakan hubungi admin atau coba lagi nanti.');
        }

        // Get available payment methods
        $this->paymentService = new SubscriptionPaymentService($software->company_id);
        $paymentMethods = $this->paymentService->getAvailablePaymentMethods();


        if (empty($paymentMethods)) {
            return redirect()
                ->route('customer.software.show', $slug)
                ->with('error', 'Tidak ada metode pembayaran yang tersedia. Silakan hubungi admin.');
        }

        return view('customer.checkout.show', compact('software', 'package', 'paymentMethods'));
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

        // Initialize payment service
        $this->paymentService = new SubscriptionPaymentService($software->company_id);

        // Verify payment method is available
        $availableMethods = $this->paymentService->getAvailablePaymentMethods();
        if (!isset($availableMethods[$validated['payment_gateway']])) {
            return redirect()
                ->route('customer.software.show', $slug)
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
            dd($e);
            DB::rollBack();

            // dd($e);
            Log::error('Checkout failed', [
                'user_id' => $user->id,
                'software_slug' => $slug,
                'package_id' => $packageId,
                'payment_gateway' => $validated['payment_gateway'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('customer.software.show', $slug)
                ->with('error', 'Checkout gagal: ' . $e->getMessage());
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
                    ->route('customer.payment.pending', ['order' => $subscription->order_number])
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
}