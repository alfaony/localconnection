<?php

namespace App\Http\Controllers;

use App\Models\DivisionBudget;
use App\Models\Division;
use Illuminate\Http\Request;
use Auth;

use App\Schemas\RoleSchema;
use App\Helpers\Access;

class DivisionBudgetController extends Controller
{
    public function index()
    {
        $userRoleName = Auth::user()->role->name;
        $approval = false;
        if(Access::can('approve','division_budgets'))
        {
            $approval = true;
        }

        if($userRoleName == RoleSchema::ROOT || $userRoleName == RoleSchema::ADMIN || $userRoleName == RoleSchema::DIRECTOR)
        {
            $divisionBudgets = DivisionBudget::byCompany(Auth::user()->company_id)->with('division', 'user')->orderBy('is_approved','asc')->paginate(10);
        }else
        {
            $divisionBudgets = DivisionBudget::where('user_id', Auth::id())->with('division', 'user')->orderBy('created_at','desc')->paginate(10);
        }
            return view('division_budget.index', compact('divisionBudgets','approval'));
    }

    public function create()
    {
        $user = Auth::user();
        $divisions = $user->divisions; 
        return view('division_budget.createOrEdit', compact('divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
        ]);

        DivisionBudget::create([
            'user_id' => Auth::id(),
            'division_id' => $request->division_id,
            'name' => $request->name,
            'amount' => $request->amount,
        ]);

        return redirect()->route('division-budget.index')->with('store', 'Pengajuan anggaran berhasil dibuat.');
    }

    public function edit($slug)
    {
        $user = Auth::user();
        $divisions = $user->divisions; 
        $divisionBudget = DivisionBudget::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        if($divisionBudget->is_approved == TRUE)
        {
            return redirect()->route('division-budget.index')->with('error', 'Pengajuan anggaran tidak bisa diubah karena sudah diapprove.'); 
        }
        return view('division_budget.createOrEdit', compact('divisionBudget', 'divisions'));
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
        ]);

        $divisionBudget = DivisionBudget::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $divisionBudget->update([
            'division_id' => $request->division_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'is_approved' => NULL,
        ]);

        return redirect()->route('division-budget.index')->with('update', 'Pengajuan anggaran berhasil diubah.');
    }

    public function show($slug)
    {
        $divisionBudget = DivisionBudget::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $initialBudget = ($divisionBudget->quotes->sum('total') ?? 0) + $divisionBudget->amount;

        return view('division_budget.show', compact('divisionBudget', 'initialBudget'));
    }

    public function destroy($slug)
    {
        $divisionBudget = DivisionBudget::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        if($divisionBudget->is_approved == TRUE)
        {
            return redirect()->route('division-budget.index')->with('error', 'Pengajuan anggaran tidak bisa diubah karena sudah diapprove.'); 
        }
        $divisionBudget->delete();
        return redirect()->route('division-budget.index')->with('delete', 'Pengajuan anggaran berhasil dihapus.');
    }

    public function approve(Request $request, $slug)
    {
        $request->validate([
            'status' => 'required|in:1,0',
        ]);

        $divisionBudget = DivisionBudget::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $divisionBudget->update(['is_approved' => $request->status,'notes' => $request->notes]);

        $approvement = $request->status == 1 ? 'approve' : 'notapprove';

        return redirect()->route('division-budget.index')->with($approvement, true);
    }
}
