<?php

namespace App\Http\Controllers;

use App\Models\DivisionBudget;
use App\Models\Division;
use App\Models\SettingCompany;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers\EmailNotifHelper;
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
            $divisionBudgets = DivisionBudget::byCompany(Auth::user()->company_id)->with('division', 'user')->orderByRaw('
            CASE 
                WHEN is_approved IS NULL THEN 0 
                WHEN is_approved = 0 THEN 1 
                WHEN is_approved = 1 THEN 2 
            END ASC, 
            created_at DESC
        ')->paginate(10);
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
            'file.*' => 'nullable|file|max:1024', // Validasi untuk file
        ]);

        $files = [];
        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                if (!in_array($file->getClientOriginalExtension(), ['pdf', 'doc', 'docx', 'xls', 'xlsx'])) 
                {
                    return redirect()->back()->withErrors(['file' => 'File harus berupa PDF, DOC, DOCX, XLS, atau XLSX.']);
                }
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileName = $originalName . '_' . uniqid() . '.' . $extension;

                if($fileName)
                {
                    $path = $file->storeAs('file', $fileName, 'public');
                    $files[] = $path;
                }
            }
        }

        $budget = DivisionBudget::create([
            'user_id' => Auth::id(),
            'division_id' => $request->division_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'file' => json_encode($files), // Simpan file dalam bentuk JSON
            'description' => $request->description,
        ]);

        $this->sendNotification($budget, 'store', Auth::user()->company_id);

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
            'file.*' => 'nullable|file|max:1024', // Validasi untuk file
        ]);
        
        $divisionBudget = DivisionBudget::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();

        $divisionBudget->division_id = $request->division_id;
        $divisionBudget->name = $request->name;
        $divisionBudget->amount = $request->amount;
        $divisionBudget->is_approved = NULL;
        $divisionBudget->description = $request->description;

        if ($request->hasFile('file')) {
            $existingFiles = json_decode($divisionBudget->file, true) ?? [];
            $files = [];
            foreach ($request->file('file') as $file) {
                if (!in_array($file->getClientOriginalExtension(), ['pdf', 'doc', 'docx', 'xls', 'xlsx'])) 
                {
                    return redirect()->back()->withErrors(['file' => 'File harus berupa PDF, DOC, DOCX, XLS, atau XLSX.']);
                }

                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $fileName = $originalName . '_' . uniqid() . '.' . $extension;

                // Validation File
                if($fileName)
                {
                    $path = $file->storeAs('file', $fileName, 'public');
                    $files[] = $path;
                }
            }
            $allFiles = array_merge($existingFiles, $files);
            $divisionBudget->file = json_encode($allFiles);
        }

        $divisionBudget->save();
        $this->sendNotification($divisionBudget, 'update', Auth::user()->company_id);

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

        $this->sendNotification($divisionBudget, $approvement, Auth::user()->company_id,true,$request->notes);

        return redirect()->route('division-budget.index')->with($approvement, true);
    }

    protected function sendNotification($budget, $timeNotify, $companyId, $approval = null,  $notes = null)
    {
        $data = [
            'name' => $budget->name,
            'budget' => $budget->amount,
            'description' => $budget->description,
            'division' => $budget->division->name,
            'user_create' => $budget->user->name,
            'note' => $notes ?? '',
            'approver_name' => Auth::user()->name,
        ];
        
        $toEmails = [];
        $toNames = [];
        
        if(!$approval)
        {
            $ccEmails = [Auth::user()->email];
            $usersAdmin = User::where('company_id',Auth::user()->company_id)->whereHas('role', function($q){
                $q->where('name',RoleSchema::ADMIN)->orWhere('name',RoleSchema::DIRECTOR);
            })->get();

            if($usersAdmin->isEmpty())
            {
                return false;
            }

            foreach ($usersAdmin as $user) 
            {
                $toEmails[] = $user->email;
                $toNames[] = $user->name;
            }
        }else
        {
            $toEmails[] = $budget->user->email;
            $toNames[] = $budget->user->name;
            $ccEmails = [Auth::user()->email];
        }


        $smtpConfig = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        $fromEmail = $smtpConfig['username'] ?? '';
        $fromName = $smtpConfig['name'] ?? '';

        switch ($timeNotify) 
        {
            case "store":
                $subject = 'Pemberitahuan Pengajuan Anggaran '.$budget->name;
                $tamplate = 'email.notif_budgetting';
                break;

            case "update":
                $subject = 'Pemberitahuan Perubahan Anggaran '.$budget->name;
                $tamplate = 'email.notif_budgetting';
                break;

            case "approve":
                $subject = 'Anggaran '.$budget->name.' Disetujui';
                $tamplate = 'email.notif_budget_approval';
                break;

            case "notapprove":
                $subject = 'Anggaran '.$budget->name.' Tidak Disetujui';
                $tamplate = 'email.notif_budget_decline';
                break;
        }

        $data['title'] = $subject;
        
        // Email Helper Notification
        return EmailNotifHelper::sentEmail(
            $fromEmail,
            $fromName,
            $toEmails, 
            $toNames, 
            $subject,
            $tamplate,
            $data, 
            $smtpConfig, 
            $companyId, 
            $ccEmails
        );
    }
}
