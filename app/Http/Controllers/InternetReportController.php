<?php

namespace App\Http\Controllers;

use App\Models\DataCenter;
use App\Models\InternetAsset;
use App\Models\InternetCustomer;
use App\Models\InternetCustomerPurchase;
use App\Models\InternetPackage;
use App\Models\Pop;
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

    public function groupings()
    {
        $companyId = Auth::user()->company_id;
        $list = InternetCustomer::byCompany($companyId)
            ->whereNotNull('grouping_id')
            ->where('grouping_id', '!=', '')
            ->orderBy('grouping_id')
            ->pluck('grouping_id')
            ->unique()
            ->values();

        return response()->json(['success' => true, 'groupings' => $list]);
    }

    public function data(Request $request)
    {
        $from = Carbon::parse($request->input('from', now()->startOfMonth()))->startOfDay();
        $to   = Carbon::parse($request->input('to',   now()->endOfMonth()))->endOfDay();

        $companyId  = Auth::user()->company_id;
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();

        // ── Grouping filter ──────────────────────────────────────────
        $groupingId = $request->input('grouping_id', 'all'); // 'all' | 'none' | 'GRP-001' etc.

        // Applied to InternetCustomer queries directly
        $gFilter = function ($q) use ($groupingId) {
            if ($groupingId === 'none') {
                $q->where(fn($q2) => $q2->whereNull('grouping_id')->orWhere('grouping_id', ''));
            } elseif ($groupingId !== 'all') {
                $q->where('grouping_id', $groupingId);
            }
        };

        // Applied inside whereHas('customer', ...) callbacks
        $custFilter = function ($q) use ($companyIds, $gFilter) {
            $q->whereIn('company_id', $companyIds);
            $gFilter($q);
        };

        // ── Income ──────────────────────────────────────────────────
        $totalIncome = InternetCustomerPurchase::whereHas('customer', $custFilter)
            ->whereNotNull('confirmation_finance_at')
            ->whereBetween('confirmation_finance_at', [$from, $to])
            ->sum('amount_paid');

        $totalTransactions = InternetCustomerPurchase::whereHas('customer', $custFilter)
            ->whereNotNull('confirmation_finance_at')
            ->whereBetween('confirmation_finance_at', [$from, $to])
            ->count();

        // Monthly income breakdown
        $monthlyIncome = InternetCustomerPurchase::whereHas('customer', $custFilter)
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
        $incomeByMethod = InternetCustomerPurchase::whereHas('customer', $custFilter)
            ->whereNotNull('confirmation_finance_at')
            ->whereBetween('confirmation_finance_at', [$from, $to])
            ->selectRaw('payment_method, SUM(amount_paid) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // Income by package (join — apply grouping on joined table)
        $incomeByPackage = InternetCustomerPurchase::whereHas('customer', $custFilter)
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
            ->tap($gFilter)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $activatedInRange = InternetCustomer::byCompany($companyId)
            ->tap($gFilter)
            ->where('status', ParamSchema::ACTIVE)
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        $connectingInRange = InternetCustomer::byCompany($companyId)
            ->tap($gFilter)
            ->where('status', ParamSchema::REACTIVATED)
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        $churnedInRange = InternetCustomer::byCompany($companyId)
            ->tap($gFilter)
            ->whereIn('status', [ParamSchema::CANCELLED, 'closed', ParamSchema::DISCONNECTED])
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        $totalActiveNow = InternetCustomer::byCompany($companyId)
            ->tap($gFilter)
            ->where('status', ParamSchema::ACTIVE)
            ->count();

        $totalConnectingNow = InternetCustomer::byCompany($companyId)
            ->tap($gFilter)
            ->where('status', ParamSchema::REACTIVATED)
            ->count();

        // Average revenue per active customer (ARPU)
        $arpu = $totalActiveNow > 0 ? round($totalIncome / max($totalActiveNow, 1), 0) : 0;

        // ── Asset & ROI ──────────────────────────────────────────────
        $totalAssetValue  = InternetAsset::byCompany($companyId)->sum(DB::raw('unit_price * quantity'));
        $activeAssetValue = InternetAsset::byCompany($companyId)->active()->sum(DB::raw('unit_price * quantity'));

        // Monthly recurring revenue (last full month confirmed)
        $lastMonth = now()->subMonth();
        $monthlyRecurring = InternetCustomerPurchase::whereHas('customer', $custFilter)
            ->whereNotNull('confirmation_finance_at')
            ->whereMonth('confirmation_finance_at', $lastMonth->month)
            ->whereYear('confirmation_finance_at', $lastMonth->year)
            ->sum('amount_paid');

        $roiMonths = ($monthlyRecurring > 0 && $totalAssetValue > 0)
            ? round($totalAssetValue / $monthlyRecurring, 1) : null;
        $roiYears  = $roiMonths ? round($roiMonths / 12, 1) : null;

        // Historical MRR for ROI trend (12 months)
        $mrrHistory = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $rev = InternetCustomerPurchase::whereHas('customer', $custFilter)
                ->whereNotNull('confirmation_finance_at')
                ->whereMonth('confirmation_finance_at', $m->month)
                ->whereYear('confirmation_finance_at', $m->year)
                ->sum('amount_paid');
            $mrrHistory[] = ['label' => $m->format('M Y'), 'value' => (float) $rev];
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

        // ── Grouping ID breakdown ────────────────────────────────────
        $byGroupingBase = InternetCustomer::byCompany($companyId)
            ->tap($gFilter)
            ->selectRaw("
                COALESCE(grouping_id, '') as grouping_key,
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as connecting_count
            ", [ParamSchema::ACTIVE, ParamSchema::REACTIVATED])
            ->groupByRaw("COALESCE(grouping_id, '')")
            ->get()
            ->sortBy(fn($r) => [$r->grouping_key === '' ? 1 : 0, -$r->total])
            ->values();

        $revenueByGrouping = InternetCustomerPurchase::whereHas('customer', $custFilter)
            ->whereNotNull('confirmation_finance_at')
            ->whereBetween('confirmation_finance_at', [$from, $to])
            ->join('internet_customers as ic', 'internet_customer_purchases.internet_customer_id', '=', 'ic.id')
            ->selectRaw("COALESCE(ic.grouping_id, '') as grouping_key, SUM(internet_customer_purchases.amount_paid) as revenue, COUNT(*) as tx_count")
            ->groupByRaw("COALESCE(ic.grouping_id, '')")
            ->get()
            ->keyBy('grouping_key');

        $byGrouping = $byGroupingBase->map(function ($r) use ($revenueByGrouping) {
            $key = $r->grouping_key;
            $rev = $revenueByGrouping->get($key);
            return [
                'grouping_id' => $key ?: null,
                'label'       => $key ?: 'Tanpa Grouping',
                'total'       => (int) $r->total,
                'active'      => (int) $r->active_count,
                'connecting'  => (int) $r->connecting_count,
                'revenue'     => (float) ($rev->revenue ?? 0),
                'tx_count'    => (int) ($rev->tx_count ?? 0),
            ];
        });

        // ── Payment status bulan ini ─────────────────────────────────
        $pmStart = now()->startOfMonth()->startOfDay();
        $pmEnd   = now()->endOfMonth()->endOfDay();

        $paidThisMonth = InternetCustomerPurchase::whereHas('customer', $custFilter)
            ->whereNotNull('confirmation_finance_at')
            ->whereBetween('confirmation_finance_at', [$pmStart, $pmEnd])
            ->with(['customer' => fn($q) => $q->with('internetPackage')])
            ->orderByDesc('confirmation_finance_at')
            ->get();

        $paidCustomerIds = $paidThisMonth->pluck('internet_customer_id')->unique();

        $paidList = $paidThisMonth->map(fn($p) => [
            'name'           => $p->customer->name ?? '–',
            'code'           => $p->customer->code ?? '–',
            'username'       => $p->customer->username ?? '–',
            'package'        => $p->customer->internetPackage->name ?? '–',
            'grouping_id'    => $p->customer->grouping_id,
            'amount'         => (float) $p->amount_paid,
            'payment_method' => $p->payment_method,
            'paid_at'        => optional($p->confirmation_finance_at)->format('d M Y H:i'),
        ]);

        $unpaidList = InternetCustomer::byCompany($companyId)
            ->tap($gFilter)
            ->whereIn('status', [ParamSchema::ACTIVE, ParamSchema::REACTIVATED])
            ->whereNotIn('id', $paidCustomerIds->toArray())
            ->with('internetPackage')
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'name'        => $c->name,
                'code'        => $c->code,
                'username'    => $c->username,
                'package'     => $c->internetPackage->name ?? '–',
                'status'      => $c->status,
                'grouping_id' => $c->grouping_id,
            ]);

        // ── Expenses ─────────────────────────────────────────────────
        // Hitung jumlah bulan dalam periode yang dipilih
        $periodMonths = max(1, (int) round($from->diffInDays($to) / 30));

        $dataCenters = DataCenter::byCompany($companyId)
            ->select('id', 'name', 'cost_per_month', 'capacity_mb', 'tanggal_tagihan')
            ->orderBy('name')
            ->get()
            ->map(fn($dc) => [
                'id'              => $dc->id,
                'name'            => $dc->name,
                'cost_per_month'  => (float) $dc->cost_per_month,
                'total_in_period' => (float) $dc->cost_per_month * $periodMonths,
                'capacity_mb'     => $dc->capacity_mb,
                'tanggal_tagihan' => $dc->tanggal_tagihan,
            ]);

        $pops = Pop::byCompany($companyId)
            ->select('id', 'name', 'monthly_cost', 'capacity_mb', 'lease_expiration_date')
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id'                    => $p->id,
                'name'                  => $p->name,
                'monthly_cost'          => (float) $p->monthly_cost,
                'total_in_period'       => (float) $p->monthly_cost * $periodMonths,
                'capacity_mb'           => $p->capacity_mb,
                'lease_expiration_date' => $p->lease_expiration_date,
            ]);

        $totalDataCenterMonthly = $dataCenters->sum('cost_per_month');
        $totalPopMonthly        = $pops->sum('monthly_cost');
        $totalExpenseMonthly    = $totalDataCenterMonthly + $totalPopMonthly;
        $totalExpensePeriod     = $totalExpenseMonthly * $periodMonths;
        $netIncome              = (float) $totalIncome - $totalExpensePeriod;

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
                    'new'                  => $newCustomers,
                    'activated'            => $activatedInRange,
                    'connecting'           => $connectingInRange,
                    'churned'              => $churnedInRange,
                    'total_active_now'     => $totalActiveNow,
                    'total_connecting_now' => $totalConnectingNow,
                    'mrr_history'          => $mrrHistory,
                    'monthly_recurring'    => (float) $monthlyRecurring,
                    'by_grouping'          => $byGrouping->values(),
                ],
                'payment' => [
                    'month_label'   => now()->translatedFormat('F Y'),
                    'paid_count'    => $paidCustomerIds->count(),
                    'paid_total'    => (float) $paidThisMonth->sum('amount_paid'),
                    'paid_list'     => $paidList->values(),
                    'unpaid_count'  => $unpaidList->count(),
                    'unpaid_list'   => $unpaidList->values(),
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
                'expense' => [
                    'period_months'            => $periodMonths,
                    'data_center_monthly'      => $totalDataCenterMonthly,
                    'pop_monthly'              => $totalPopMonthly,
                    'total_monthly'            => $totalExpenseMonthly,
                    'total_period'             => $totalExpensePeriod,
                    'net_income'               => $netIncome,
                    'data_centers'             => $dataCenters,
                    'pops'                     => $pops,
                ],
            ],
        ]);
    }
}
