<?php

namespace App\Http\Controllers;

use App\Models\DivisionBudget;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'file.*' => 'nullable|mimes:pdf,doc,docx,xls,xlsx|max:1024', // Validasi untuk file
        ]);

        $files = [];
        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileName = $originalName . '_' . uniqid() . '.' . $extension;
                $path = $file->storeAs('file', $fileName, 'public');
                $files[] = $path;
            }
        }

        DivisionBudget::create([
            'user_id' => Auth::id(),
            'division_id' => $request->division_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'file' => json_encode($files), // Simpan file dalam bentuk JSON
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

        $divisionBudget->division_id = $request->division_id;
        $divisionBudget->name = $request->name;
        $divisionBudget->amount = $request->amount;
        $divisionBudget->is_approved = NULL;

        if ($request->hasFile('file')) {
            $existingFiles = json_decode($divisionBudget->file, true) ?? [];
            $newFiles = [];
            foreach ($request->file('file') as $file) {
                $filename = $file->getClientOriginalName();
                $uniqueFilename = pathinfo($filename, PATHINFO_FILENAME) . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('files', $uniqueFilename);
                $newFiles[] = $path;
            }
            $allFiles = array_merge($existingFiles, $newFiles);
            $divisionBudget->file = json_encode($allFiles);
        }

        $divisionBudget->save();

        return redirect()->route('division-budget.index')->with('update', 'Pengajuan anggaran berhasil diubah.');
    }

    public function show($slug)
    {
        $divisionBudget = DivisionBudget::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $initialBudget = ($divisionBudget->quotes->sum('total') ?? 0) + $divisionBudget->amount;

        return view('division_budget.show', compact('divisionBudget', 'initialBudget'));
    }

    public function destroy(Request $request, $slug)
    {
        $divisionBudget = DivisionBudget::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();

        if($request->action)
        {
            $files = json_decode($divisionBudget->file);

            $filePath = urldecode($request->file);
            if (($key = array_search($filePath, $files)) !== false) {
                unset($files[$key]);
                Storage::delete($filePath);
                $divisionBudget->file = json_encode(array_values($files));
                $divisionBudget->save();
            }

            return redirect()->back()->with('delete', 'File berhasil dihapus.');
        }else
        {
            if($divisionBudget->is_approved == TRUE)
            {
                return redirect()->route('division-budget.index')->with('error', 'Pengajuan anggaran tidak bisa diubah karena sudah diapprove.'); 
            }

            if ($divisionBudget->file) {
                $files = json_decode($divisionBudget->file);
                foreach ($files as $file) 
                {
                    Storage::delete($file);
                }
            }
    
            $divisionBudget->delete();
            return redirect()->route('division-budget.index')->with('delete', 'Pengajuan anggaran berhasil dihapus.');
        }
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
