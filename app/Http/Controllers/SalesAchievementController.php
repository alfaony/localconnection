<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\SalesAchievement;
use App\Schemas\ParamSchema;

class SalesAchievementController extends Controller
{
    public function index(Request $request)
    {
        $status = config('custom.statusApproval');
        $query = SalesAchievement::query();

        if($request->status)
        {
            $query->where('status',$request->status);
        }

        $achievements = $query->byUserAndApproval(Auth::user()->id)->paginate(10);
        return view('sales_achievement.index', compact('achievements','status'));
    }

    public function create()
    {
        $months = config('custom.month');
        return view('sales_achievement.createOrEdit',compact('months'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'period' => 'required|string',
            'sales_amount' => 'required|numeric',
            'total_presentations' => 'required|integer',
            'total_offers_issued' => 'required|integer'
        ]);

        $salesAchievement = new SalesAchievement();
        $salesAchievement->user_id = Auth::id(); // secara otomatis mengatur user_id ke pengguna yang sedang login
        $salesAchievement->approval_user_id = Auth::id(); // secara otomatis mengatur user_id ke pengguna yang sedang login
        $salesAchievement->period = $request->period;
        $salesAchievement->sales_amount = $request->sales_amount;
        $salesAchievement->total_presentations = $request->total_presentations;
        $salesAchievement->total_offers_issued = $request->total_offers_issued;
        $salesAchievement->approved = false;  // mengatur ke 'false' secara default
        $salesAchievement->points = null;  // awalnya tidak ada poin
        $salesAchievement->status = ParamSchema::INREVIEW;
        $salesAchievement->save();

        return redirect()->route('sales_achievement.index')->with('success', 'Sales Achievement created successfully.');
    }

    public function show($slug)
    {
        $salesAchievement = SalesAchievement::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        return view('sales_achievement.show', compact('salesAchievement'));
    }

    public function edit($slug)
    {
        $months = config('custom.month');
        $salesAchievement = SalesAchievement::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        return view('sales_achievement.createOrEdit', compact('salesAchievement','months'));
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'period' => 'required|string',
            'sales_amount' => 'required|numeric',
            'total_presentations' => 'required|integer',
            'total_offers_issued' => 'required|integer'
        ]);

        $salesAchievement = SalesAchievement::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

        $salesAchievement->user_id = Auth::id(); // secara otomatis mengatur user_id ke pengguna yang sedang login
        $salesAchievement->approval_user_id = Auth::id(); // secara otomatis mengatur user_id ke pengguna yang sedang login
        $salesAchievement->period = $request->period;
        $salesAchievement->sales_amount = $request->sales_amount;
        $salesAchievement->total_presentations = $request->total_presentations;
        $salesAchievement->total_offers_issued = $request->total_offers_issued;
        $salesAchievement->save();

        return redirect()->route('sales_achievement.index')->with('success', 'Sales Achievement updated successfully.');
    }

    public function destroy($slug)
    {
        $salesAchievement = SalesAchievement::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $salesAchievement->delete();

        return redirect()->route('sales_achievement.index')->with('success', 'Sales Achievement deleted successfully.');
    }

    public function addPoint(Request $request, $slug)
    {
        $request->validate([
            'point' => 'required|integer',
        ]);

        $salesAchievement = SalesAchievement::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $salesAchievement->approval_user_id = Auth::user()->id;
        $salesAchievement->points = $request->point;
        $salesAchievement->status = ParamSchema::COMPLATE;
        $salesAchievement->approved = true;
        $salesAchievement->save();


        return redirect()->route('sales_achievement.show',$salesAchievement->slug)->with('success', 'Point Pencapaian Penjualan Berhasil Ditambahkan.');
    }
}

