<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Schemas\RoleSchema;
use App\Models\Subscription;

use App\Helpers\InboxHelper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
                ->route('customer-subscription.index')
                ->with('info', 'Order number tidak ditemukan');
        }

        // Find subscription by order number
        $subscription = CustomerSubscription::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->first();

        if (!$subscription) {
            return redirect()
                ->route('customer-subscription.index')
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
                ->route('customer-subscription.index')
                ->with('error', 'Order number tidak ditemukan');
        }

        // Find subscription by order number
        $subscription = CustomerSubscription::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->first();

        if (!$subscription) {
            return redirect()
                ->route('customer-subscription.index')
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

    /**
     * Upload proof of transfer for manual payment
     */
    public function uploadProof(Request $request, $paymentId)
    {
        $request->validate([
            'transfer_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'sender_name' => 'required|string|max:255',
            'sender_bank' => 'required|string|max:255',
        ]);

        // Find payment and verify ownership
        $payment = SubscriptionPayment::findOrFail($paymentId);
        
        $subscription = $payment->subscription;
        
        if ($subscription->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        // Guard: reject if slot reservation has expired
        if ($subscription->isSlotExpired()) {
            return back()->with('error', '⏰ Waktu reservasi slot sudah habis. Pembayaran tidak bisa diproses.');
        }

        // Verify it's a manual transfer payment
        if (!$payment->isManualTransfer()) {
            return back()->with('error', 'Upload bukti transfer hanya untuk pembayaran manual');
        }

        // Verify payment is still pending
        if (!$payment->isPending()) {
            return back()->with('error', 'Pembayaran sudah diproses');
        }

        try {
            // Delete old proof if exists
            if ($payment->manual_transfer_proof) {
                Storage::disk('s3')->delete($payment->manual_transfer_proof);
            }

            // Store new proof to S3
            $file = $request->file('transfer_proof');
            $filename = 'transfer_proof_' . $payment->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = 'subscription_payments/transfer_proofs/' . $filename;
            
            // Upload to S3
            Storage::disk('s3')->put($path, file_get_contents($file), 'public');

            // Update payment record
            $payment->update([
                'manual_transfer_proof' => $path,
                'manual_transfer_sender_name' => $request->sender_name,
                'manual_transfer_sender_bank' => $request->sender_bank,
            ]);

            Log::info('Transfer proof uploaded', [
                'payment_id' => $payment->id,
                'subscription_id' => $subscription->id,
                'user_id' => Auth::id(),
                'file_path' => $path,
                'sender_name' => $request->sender_name,
                'sender_bank' => $request->sender_bank,
            ]);

            $this->notifyFinanceTeamSuccess($subscription);

            return back()->with('success', 'Bukti transfer berhasil diupload. Menunggu verifikasi admin.');

        } catch (\Exception $e) {
            Log::error('Failed to upload transfer proof', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal mengupload bukti transfer. Silakan coba lagi.');
        }
    }

    protected function notifyFinanceTeamSuccess($subscription)
    {
        $userFinance = User::whereHas('role.permissions', function ($q) {
            $q->where('method', 'manual-approve')->where('table', 'subscriptions');
        })
        ->where(function ($q) use ($subscription) {
            $q->where('company_id', $subscription->company_id)
            ->orWhereHas('accessibleCompanies', function ($sub) use ($subscription) {
                $sub->where('companies.id', $subscription->company_id);
            });
        })
        ->get();

        if($userFinance->isNotEmpty()) {
            $userFinance = $userFinance->pluck('id')->unique();

            $from = User::where('company_id', $subscription->company_id)
                ->whereHas('role', function ($q) {
                    $q->whereIn('name', [RoleSchema::SYSTEM_BOS, RoleSchema::ROOT, RoleSchema::ADMIN]);
                })
                ->first();

            $message = "Pelanggan ".$subscription->user->name." telah berhasil melakukan pembayaran, Silahkan ditindaklanjuti.";
            $directUrl = route('subscription.payments', $subscription->id);
            
            foreach($userFinance as $finance) {
                $this->sentInbox($finance, Auth::id(), $message, $directUrl);
            }   
        }
    }

    private function sentInbox($to,$from, $message,$directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent($to, $from, $message, $directUrl);
        return true;
    }

}
