<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternetCustomer;
use App\Models\InternetCustomerPurchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;

        $stats = [
            'total_customers'    => InternetCustomer::where('company_id', $companyId)->count(),
            'active_customers'   => InternetCustomer::where('company_id', $companyId)->where('status', 'active')->count(),
            'isolir_customers'   => InternetCustomer::where('company_id', $companyId)->where('status', 'isolir')->count(),
            'revenue_this_month' => InternetCustomerPurchase::whereHas('internetCustomer', fn($q) => $q->where('company_id', $companyId))
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->where('status', 'paid')
                ->sum('amount'),
        ];

        $recentCustomers = InternetCustomer::with('internetPackage')
            ->where('company_id', $companyId)
            ->latest()
            ->limit(10)
            ->get();

        $statusSummary = InternetCustomer::where('company_id', $companyId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.dashboard', compact('stats', 'recentCustomers', 'statusSummary'));
    }
}
