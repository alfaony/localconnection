<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Software;
use App\Models\CustomerSubscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard with analytics
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;

        // Stats cards
        $stats = [
            'total_revenue_today' => $this->getTotalRevenue($companyId, 'today'),
            'total_revenue_month' => $this->getTotalRevenue($companyId, 'month'),
            'total_active_subscriptions' => $this->getActiveSubscriptions($companyId),
            'total_customers' => $this->getTotalCustomers($companyId),
        ];

        // Revenue chart data (last 30 days)
        $revenueChart = $this->getRevenueChartData($companyId);

        // Subscription trend chart (last 6 months)
        $subscriptionTrendChart = $this->getSubscriptionTrendData($companyId);

        // Top software by subscription count
        $topSoftware = $this->getTopSoftware($companyId);

        // Recent payments
        $recentPayments = SubscriptionPayment::byCompany($companyId)
            ->with(['subscription.user', 'subscription.masterAccount.software'])
            ->paid()
            ->latest()
            ->limit(10)
            ->get();

        // Subscriptions expiring soon (next 7 days)
        $expiringSoon = CustomerSubscription::byCompany($companyId)
            ->expiringSoon(7)
            ->with(['user', 'masterAccount.software', 'package'])
            ->get();

        // Slot usage overview
        $slotUsage = $this->getSlotUsageOverview($companyId);

        return view('admin.dashboard', compact(
            'stats',
            'revenueChart',
            'subscriptionTrendChart',
            'topSoftware',
            'recentPayments',
            'expiringSoon',
            'slotUsage'
        ));
    }

    /**
     * Get total revenue
     */
    protected function getTotalRevenue($companyId, $period)
    {
        $query = SubscriptionPayment::byCompany($companyId)->paid();

        if ($period === 'today') {
            $query->whereDate('paid_at', Carbon::today());
        } elseif ($period === 'month') {
            $query->whereMonth('paid_at', Carbon::now()->month)
                  ->whereYear('paid_at', Carbon::now()->year);
        }

        return $query->sum('amount');
    }

    /**
     * Get active subscriptions count
     */
    protected function getActiveSubscriptions($companyId)
    {
        return CustomerSubscription::byCompany($companyId)
            ->active()
            ->paid()
            ->count();
    }

    /**
     * Get total customers count
     */
    protected function getTotalCustomers($companyId)
    {
        return CustomerSubscription::byCompany($companyId)
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * Get revenue chart data for last 30 days
     */
    protected function getRevenueChartData($companyId)
    {
        $data = SubscriptionPayment::byCompany($companyId)
            ->paid()
            ->where('paid_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d M');
            })->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ];
    }

    /**
     * Get subscription trend data for last 6 months
     */
    protected function getSubscriptionTrendData($companyId)
    {
        $data = CustomerSubscription::byCompany($companyId)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return [
            'labels' => $data->pluck('month')->map(function($month) {
                return Carbon::parse($month . '-01')->format('M Y');
            })->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ];
    }

    /**
     * Get top software by subscription count
     */
    protected function getTopSoftware($companyId, $limit = 5)
    {
        return Software::byCompany($companyId)
            ->withCount(['masterAccounts as subscriptions_count' => function($query) {
                $query->join('customer_subscriptions', 'master_accounts.id', '=', 'customer_subscriptions.master_account_id')
                      ->where('customer_subscriptions.status', 'active')
                      ->where('customer_subscriptions.payment_status', 'paid');
            }])
            ->orderBy('subscriptions_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get slot usage overview
     */
    protected function getSlotUsageOverview($companyId)
    {
        return Software::byCompany($companyId)
            ->with(['masterAccounts' => function($query) {
                $query->where('status', 'active');
            }])
            ->get()
            ->map(function($software) {
                $totalSlots = $software->masterAccounts->sum('max_slots');
                $usedSlots = $software->masterAccounts->sum('used_slots');
                
                return [
                    'software' => $software->nama . ' - ' . $software->tipe_paket,
                    'total_slots' => $totalSlots,
                    'used_slots' => $usedSlots,
                    'available_slots' => $totalSlots - $usedSlots,
                    'usage_percentage' => $totalSlots > 0 ? round(($usedSlots / $totalSlots) * 100, 2) : 0,
                ];
            });
    }
}