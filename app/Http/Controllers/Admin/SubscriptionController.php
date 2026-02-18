<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Models\Software;
use App\Models\MasterAccount;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Helpers\InboxHelper;
use App\Schemas\RoleSchema;
use App\Models\User;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display a listing of subscriptions
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $query = CustomerSubscription::byCompany($companyId)
            ->with(['user', 'masterAccount.software', 'package', 'latestPayment']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by software
        if ($request->filled('software_id')) {
            $query->whereHas('masterAccount', function($q) use ($request) {
                $q->where('software_id', $request->software_id);
            });
        }

        // Search by order number or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $subscriptions = $query->latest()->paginate(20);

        // Get softwares for filter
        $softwares = Software::byCompany($companyId)->active()->get();

        return view('admin.subscriptions.index', compact('subscriptions', 'softwares'));
    }

    /**
     * Display the specified subscription
     */
    public function show(CustomerSubscription $subscription)
    {
        $this->access('view', $subscription);

        $subscription->load([
            'user',
            'masterAccount.software',
            'package',
            'payments' => function($query) {
                $query->latest();
            }
        ]);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    /**
     * Show form to manually extend subscription
     */
    public function editExpiry(CustomerSubscription $subscription)
    {
        $this->access('update', $subscription);

        return view('admin.subscriptions.edit-expiry', compact('subscription'));
    }

    /**
     * Manually extend subscription expiry date
     */
    public function updateExpiry(Request $request, CustomerSubscription $subscription)
    {
        try {
            $this->access('update', $subscription);
    
            $validated = $request->validate([
                'tanggal_expired' => 'required|date|after:today',
            ]);
    
            $subscription->update([
                'tanggal_expired' => $validated['tanggal_expired'],
                'status' => 'active', // Reactivate if was expired
            ]);
            
            return redirect()
                ->route('subscription.show', $subscription)
                ->with('success', 'Tanggal expired berhasil diupdate');
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            \Log::error($th);
            return redirect()->back()->with('error', "Gagal update tanggal expired");
        }
    }

    /**
     * Show form to change master account
     */
    public function editMasterAccount(CustomerSubscription $subscription)
    {
        $this->access('update', $subscription);

        $companyId = Auth::user()->company_id;
        $software = $subscription->masterAccount->software;


        // Get available master accounts for the same software
        $masterAccounts = MasterAccount::byCompany($companyId)
            ->where('software_id', $software->id)
            ->where('status', 'active')
            ->where('id', '!=', $subscription->master_account_id)
            ->get();

        return view('admin.subscriptions.edit-master-account', compact('subscription', 'masterAccounts'));
    }

    /**
     * Change subscription master account
     */
    public function updateMasterAccount(Request $request, CustomerSubscription $subscription)
    {
        $this->access('update', $subscription);

        $validated = $request->validate([
            'master_account_id' => 'required|exists:master_accounts,id',
        ]);
        try {
            $newMasterAccount = MasterAccount::findOrFail($validated['master_account_id']);
            
            // Check if new master account has available slots
            if (!$newMasterAccount->hasSlotsAvailable()) {
                return redirect()
                    ->back()
                    ->with('error', 'Master Account yang dipilih tidak memiliki slot tersedia');
            }
    // 
            // Release slot from old master account
            $oldMasterAccount = $subscription->masterAccount;
            $oldMasterAccount->releaseSlot();
    
            // Reserve slot in new master account
            $newMasterAccount->reserveSlot();
    
            // Update subscription
            $subscription->update([
                'master_account_id' => $newMasterAccount->id,
            ]);
    
            return redirect()
                ->route('subscription.show', $subscription)
                ->with('success', 'Master Account berhasil diubah');
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);

            \Log::error($th);
            return redirect()->back()>with('error', 'Master Account gagal diubah');
        }
    }

    /**
     * Suspend subscription manually
     */
    public function suspend(CustomerSubscription $subscription)
    {
        $this->access('update', $subscription);

        $result = $this->subscriptionService->suspendSubscription($subscription, 'Suspended by admin');

        if ($result['success']) {
            return redirect()
                ->back()
                ->with('success', 'Subscription berhasil di-suspend');
        }

        return redirect()
            ->back()
            ->with('error', $result['message']);
    }

    /**
     * Activate suspended subscription
     */
    public function activate(CustomerSubscription $subscription)
    {
        $this->access('update', $subscription);

        // Check if master account has available slots
        if (!$subscription->masterAccount->hasSlotsAvailable()) {
            return redirect()
                ->back()
                ->with('error', 'Master Account tidak memiliki slot tersedia');
        }

        // Reserve slot
        $subscription->masterAccount->reserveSlot();

        // Activate subscription
        $subscription->update([
            'status' => 'active',
        ]);

        return redirect()
            ->back()
            ->with('success', 'Subscription berhasil diaktifkan');
    }

    /**
     * View payment history
     */
    public function payments(CustomerSubscription $subscription)
    {
        $this->access('view', $subscription);

        $payments = $subscription->payments()
            ->latest()
            ->paginate(15);

        return view('admin.subscriptions.payments', compact('subscription', 'payments'));
    }

    /**
     * Manual approve payment
     */
    public function manualApprove($paymentId)
    {
        try {
            $payment = \App\Models\SubscriptionPayment::findOrFail($paymentId);
            
            $subscription = $payment->subscription;

            // Check if payment is pending
            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is not pending'
                ], 400);
            }

            // Update payment status to paid
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Always update payment_status to paid
            $updateData = [
                'payment_status' => 'paid',
            ];

            // Activate subscription if not active
            if ($subscription->status !== 'active') {
                $updateData['status'] = 'active';
                
                // Set start and end dates if not set
                if (!$subscription->tanggal_mulai) {
                    $updateData['tanggal_mulai'] = now();
                }
                
                if (!$subscription->tanggal_expired && $subscription->package) {
                    $updateData['tanggal_expired'] = now()->addDays($subscription->package->durasi_hari);
                }
            }

            $subscription->update($updateData);

            // Reserve slot in master account if needed
            if ($subscription->masterAccount && $subscription->masterAccount->hasSlotsAvailable()) {
                $subscription->masterAccount->reserveSlot();
            }

            \Log::info('Payment manually approved', [
                'payment_id' => $payment->id,
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->fresh()->status,
                'payment_status' => $subscription->fresh()->payment_status,
                'approved_by' => \Auth::id(),
            ]);

            // Send inbox notifications
            $this->notifyTeamSuccess($subscription->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Payment approved successfully'
            ]);

        } catch (\Exception $e) {
            // dd($e); 

            \Log::error('Manual approve payment failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve payment: ' . $e->getMessage()
            ], 500);
        }
    }

    private function access($action, $subscription)
    {
        return true;
    }

    /**
     * Send inbox notifications after manual approve:
     * 1. To the customer: payment approved
     * 2. To PIC software (from software.pic relation): please follow up
     */
    protected function notifyTeamSuccess($subscription)
    {
        $subscription->load(['user', 'package.software.pic', 'masterAccount.software.pic']);

        $approvedBy = Auth::id();
        $customer   = $subscription->user;
        $paket      = $subscription->package->nama_paket ?? 'Langganan';

        // Resolve software object (prefer from masterAccount, fallback to package)
        $softwareModel = $subscription->masterAccount->software
                      ?? $subscription->package->software
                      ?? null;
        $softwareName  = $softwareModel->nama ?? 'Software';

        // ── 1. Notifikasi ke Pelanggan ──────────────────────────────────────
        $urlCustomer = route('customer-software.subscription.show', $subscription->id);
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

    private function sentInbox($to, $from, $message, $directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent($to, $from, $message, $directUrl);
        return true;
    }
}