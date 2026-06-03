<?php

namespace App\Http\Controllers;

use App\Models\InternetAsset;
use App\Models\InternetCustomer;
use App\Models\InternetCustomerPurchase;
use App\Models\InternetPackage;
use App\Schemas\ParamSchema;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InternetReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'ip.restriction']);
    }

    public function index()
    {
        return view('internet-report.index');
    }

    public function data(Request $request)
    {
        $from = Carbon::parse($request->input('from', now()->startOfMonth()))->startOfDay();
        $to   = Carbon::parse($request->input('to',   now()->endOfMonth()))->endOfDay();

        $companyId  = Auth::user()->company_id;
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();

        // ── Income ──────────────────────────────────────────────────
        $totalIncome = InternetCustomerPurchase::whereHas('customer', fn($q) => $q->whereIn('company_id', $companyIds))
            ->whereNotNull('confirmation_finance_at')
            ->whereBetween('confirmation_finance_at', [$from, $to])
            ->sum('amount_paid');

        $totalTransactions = InternetCustomerPurchase::whereHas('customer', fn($q) => $q->whereIn('company_id', $companyIds))
            ->whereNotNull('confirmation_finance_at')
            ->whereBetween('confirmation_finance_at', [$from, $to])
            ->count();

        // Monthly income breakdown
        $monthlyIncome = InternetCustomerPurchase::whereHas('customer', fn($q) => $q->whereIn('company_id', $companyIds))
            ->whereNotNull('confirmation_finance_at')
            ->whereBetween('confirmation_finance_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(confirmation_finance_at, '%Y-%m') as month, SUM(amount_paid) as total, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'month' => Carbon::parse($r->month . '-01')->format('M Y'),
                'total' => (float) $r->total,
                'count' => $r->count,
            ]);

        // Income by payment method
        $incomeByMethod = InternetCustomerPurchase::whereHas('customer', fn($q) => $q->whereIn('company_id', $companyIds))
            ->whereNotNull('confirmation_finance_at')
            ->whereBetween('confirmation_finance_at', [$from, $to])
            ->selectRaw('payment_method, SUM(amount_paid) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // Income by package
        $incomeByPackage = InternetCustomerPurchase::whereHas('customer', fn($q) => $q->whereIn('company_id', $companyIds))
            ->whereNotNull('confirmation_finance_at')
            ->whereBetween('confirmation_finance_at', [$from, $to])
            ->join('internet_customers', 'internet_customer_purchases.internet_customer_id', '=', 'internet_customers.id')
            ->join('internet_packages', 'internet_customers.internet_package_id', '=', 'internet_packages.id')
            ->selectRaw('internet_packages.name as package_name, SUM(internet_customer_purchases.amount_paid) as total, COUNT(*) as count')
            ->groupBy('internet_packages.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // ── Customer Stats ───────────────────────────────────────────
        $newCustomers = InternetCustomer::byCompany($companyId)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $activatedInRange = InternetCustomer::byCompany($companyId)
            ->whereIn('status', [ParamSchema::ACTIVE, ParamSchema::REACTIVATED])
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        $churnedInRange = InternetCustomer::byCompany($companyId)
            ->whereIn('status', [ParamSchema::CANCELLED, 'closed', ParamSchema::DISCONNECTED])
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        $totalActiveNow = InternetCustomer::byCompany($companyId)
            ->whereIn('status', [ParamSchema::ACTIVE, ParamSchema::REACTIVATED])
            ->count();

        // Average revenue per active customer (ARPU)
        $arpu = $totalActiveNow > 0 ? round($totalIncome / max($totalActiveNow, 1), 0) : 0;

        // ── Asset & ROI ──────────────────────────────────────────────
        $totalAssetValue = InternetAsset::byCompany($companyId)->sum(DB::raw('unit_price * quantity'));
        $activeAssetValue = InternetAsset::byCompany($companyId)->active()->sum(DB::raw('unit_price * quantity'));

        // Monthly recurring revenue (last full month confirmed)
        $lastMonth = now()->subMonth();
        $monthlyRecurring = InternetCustomerPurchase::whereHas('customer', fn($q) => $q->whereIn('company_id', $companyIds))
            ->whereNotNull('confirmation_finance_at')
            ->whereMonth('confirmation_finance_at', $lastMonth->month)
            ->whereYear('confirmation_finance_at', $lastMonth->year)
            ->sum('amount_paid');

        // ROI months: total_asset / monthly_recurring_revenue
        $roiMonths      = ($monthlyRecurring > 0 && $totalAssetValue > 0)
            ? round($totalAssetValue / $monthlyRecurring, 1) : null;
        $roiYears       = $roiMonths ? round($roiMonths / 12, 1) : null;

        // Historical MRR for ROI trend (12 months)
        $mrrHistory = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $rev = InternetCustomerPurchase::whereHas('customer', fn($q) => $q->whereIn('company_id', $companyIds))
                ->whereNotNull('confirmation_finance_at')
                ->whereMonth('confirmation_finance_at', $m->month)
                ->whereYear('confirmation_finance_at', $m->year)
                ->sum('amount_paid');
            $mrrHistory[] = [
                'label' => $m->format('M Y'),
                'value' => (float) $rev,
            ];
        }

        // Asset by category
        $assetByCategory = InternetAsset::byCompany($companyId)
            ->selectRaw('category, COUNT(*) as count, SUM(unit_price * quantity) as value')
            ->groupBy('category')
            ->orderByDesc('value')
            ->get()
            ->map(fn($r) => [
                'category' => $r->category,
                'label'    => InternetAsset::categoryOptions()[$r->category] ?? $r->category,
                'count'    => $r->count,
                'value'    => (float) $r->value,
            ]);

        $assetStats = InternetAsset::byCompany($companyId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(unit_price * quantity) as total_value,
                COUNT(CASE WHEN status='active' THEN 1 END) as active,
                COUNT(CASE WHEN status='damaged' THEN 1 END) as damaged,
                COUNT(CASE WHEN status='maintenance' THEN 1 END) as maintenance,
                COUNT(CASE WHEN status='sold' THEN 1 END) as sold
            ")
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'from'  => $from->format('d M Y'),
                    'to'    => $to->format('d M Y'),
                    'label' => $from->format('d M Y') . ' – ' . $to->format('d M Y'),
                ],
                'income' => [
                    'total'           => (float) $totalIncome,
                    'total_tx'        => $totalTransactions,
                    'arpu'            => (float) $arpu,
                    'monthly'         => $monthlyIncome,
                    'by_method'       => $incomeByMethod,
                    'by_package'      => $incomeByPackage,
                ],
                'customer' => [
                    'new'             => $newCustomers,
                    'activated'       => $activatedInRange,
                    'churned'         => $churnedInRange,
                    'total_active_now'=> $totalActiveNow,
                    'mrr_history'     => $mrrHistory,
                    'monthly_recurring' => (float) $monthlyRecurring,
                ],
                'asset' => [
                    'total_value'     => (float) $totalAssetValue,
                    'active_value'    => (float) $activeAssetValue,
                    'by_category'     => $assetByCategory,
                    'stats'           => $assetStats,
                ],
                'roi' => [
                    'monthly_recurring' => (float) $monthlyRecurring,
                    'total_asset'       => (float) $totalAssetValue,
                    'roi_months'        => $roiMonths,
                    'roi_years'         => $roiYears,
                    'recovered_pct'     => $totalAssetValue > 0
                        ? min(100, round(($monthlyRecurring * 12) / $totalAssetValue * 100, 1))
                        : 0,
                ],
            ],
        ]);
    }
}
