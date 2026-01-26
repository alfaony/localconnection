<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionPaymentController extends Controller
{
    /**
     * Handle payment success redirect from Xendit
     */
    public function success(Request $request)
    {
        $orderNumber = $request->query('order');

        if (!$orderNumber) {
            return redirect()
                ->route('customer.subscriptions.index')
                ->with('info', 'Order number tidak ditemukan');
        }

        // Find subscription by order number
        $subscription = CustomerSubscription::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->first();

        if (!$subscription) {
            return redirect()
                ->route('customer.subscriptions.index')
                ->with('error', 'Subscription tidak ditemukan');
        }

        // Check if payment is already processed
        if ($subscription->payment_status === 'paid') {
            return view('customer.payment.success', compact('subscription'))
                ->with('info', 'Pembayaran Anda sudah diproses sebelumnya');
        }

        // Payment is still being processed
        return view('customer.payment.success', compact('subscription'))
            ->with('info', 'Pembayaran Anda sedang diproses. Silakan tunggu konfirmasi via email.');
    }

    /**
     * Handle payment failure redirect from Xendit
     */
    public function failed(Request $request)
    {
        $orderNumber = $request->query('order');

        if (!$orderNumber) {
            return redirect()
                ->route('customer.subscriptions.index')
                ->with('error', 'Order number tidak ditemukan');
        }

        // Find subscription by order number
        $subscription = CustomerSubscription::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->first();

        if (!$subscription) {
            return redirect()
                ->route('customer.subscriptions.index')
                ->with('error', 'Subscription tidak ditemukan');
        }

        return view('customer.payment.failed', compact('subscription'));
    }

    /**
     * Check payment status via AJAX
     */
    public function checkStatus($orderNumber)
    {
        $subscription = CustomerSubscription::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'status' => $subscription->status,
            'payment_status' => $subscription->payment_status,
            'is_paid' => $subscription->payment_status === 'paid',
        ]);
    }
}