<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Software;
use App\Models\MasterAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterAccountController extends Controller
{
    /**
     * Display a listing of master accounts
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $query = MasterAccount::byCompany($companyId)
            ->with(['software', 'activeSubscriptions']);

        // Filter by software
        if ($request->filled('software_id')) {
            $query->where('software_id', $request->software_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama_akun', 'like', "%{$search}%");
        }

        $masterAccounts = $query->latest()->paginate(15);

        // Get softwares for filter dropdown
        $softwares = Software::byCompany($companyId)
            ->active()
            ->get();

        return view('admin.master-accounts.index', compact('masterAccounts', 'softwares'));
    }

    /**
     * Show the form for creating a new master account
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        
        $softwares = Software::byCompany($companyId)
            ->active()
            ->get();

        return view('admin.master-accounts.create', compact('softwares'));
    }

    /**
     * Store a newly created master account
     */
    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'software_id' => 'required|exists:softwares,id',
            'nama_akun' => 'required|string|max:255',
            'max_slots' => 'required|integer|min:1',
            
            // Flexible fields
            'email_akun' => 'nullable|string',
            'password_akun' => 'nullable|string',
            'pin_code' => 'nullable|string|max:255',
            'link_invite' => 'nullable|string',
            'instruksi_akses' => 'nullable|string', // CKEditor content
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            
            'status' => 'required|in:active,inactive',
        ]);

        // Verify software belongs to company
        $software = Software::byCompany($companyId)
            ->findOrFail($validated['software_id']);

        // Handle attachment upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/master-accounts', $fileName);
            $validated['attachment'] = 'master-accounts/' . $fileName;
        }

        $validated['company_id'] = $companyId;
        $validated['used_slots'] = 0;

        $masterAccount = MasterAccount::create($validated);

        return redirect()
            ->route('admin.master-accounts.index')
            ->with('success', 'Master Account berhasil ditambahkan');
    }

    /**
     * Display the specified master account
     */
    public function show(MasterAccount $masterAccount)
    {
        // $this->authorize('view', $masterAccount);

        $masterAccount->load([
            'software',
            'activeSubscriptions.user',
            'activeSubscriptions.package'
        ]);

        return view('admin.master-accounts.show', compact('masterAccount'));
    }

    /**
     * Show the form for editing the specified master account
     */
    public function edit(MasterAccount $masterAccount)
    {
        // $this->authorize('update', $masterAccount);

        $companyId = Auth::user()->company_id;
        
        $softwares = Software::byCompany($companyId)
            ->active()
            ->get();

        return view('admin.master-accounts.edit', compact('masterAccount', 'softwares'));
    }

    /**
     * Update the specified master account
     */
    public function update(Request $request, MasterAccount $masterAccount)
    {
        // $this->authorize('update', $masterAccount);

        $validated = $request->validate([
            'software_id' => 'required|exists:softwares,id',
            'nama_akun' => 'required|string|max:255',
            'max_slots' => 'required|integer|min:1',
            
            // Flexible fields
            'email_akun' => 'nullable|string',
            'password_akun' => 'nullable|string',
            'pin_code' => 'nullable|string|max:255',
            'link_invite' => 'nullable|string',
            'instruksi_akses' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            
            'status' => 'required|in:active,inactive',
        ]);

        // Verify software belongs to company
        $software = Software::byCompany($masterAccount->company_id)
            ->findOrFail($validated['software_id']);

        // Check if max_slots is being reduced below used_slots
        if ($validated['max_slots'] < $masterAccount->used_slots) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', "Max slots tidak boleh kurang dari used slots ({$masterAccount->used_slots})");
        }

        // Handle attachment upload
        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($masterAccount->attachment) {
                \Storage::delete('public/' . $masterAccount->attachment);
            }

            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/master-accounts', $fileName);
            $validated['attachment'] = 'master-accounts/' . $fileName;
        }

        $masterAccount->update($validated);

        return redirect()
            ->route('admin.master-accounts.index')
            ->with('success', 'Master Account berhasil diupdate');
    }

    /**
     * Remove the specified master account
     */
    public function destroy(MasterAccount $masterAccount)
    {
        // $this->authorize('delete', $masterAccount);

        // Check if has active subscriptions
        $activeCount = $masterAccount->activeSubscriptions()->count();

        if ($activeCount > 0) {
            return redirect()
                ->back()
                ->with('error', 'Tidak dapat menghapus Master Account yang memiliki subscription aktif');
        }

        // Delete attachment if exists
        if ($masterAccount->attachment) {
            \Storage::delete('public/' . $masterAccount->attachment);
        }

        $masterAccount->delete();

        return redirect()
            ->route('admin.master-accounts.index')
            ->with('success', 'Master Account berhasil dihapus');
    }

    /**
     * Toggle master account status
     */
    public function toggleStatus(MasterAccount $masterAccount)
    {
        // $this->authorize('update', $masterAccount);

        $newStatus = $masterAccount->status === 'active' ? 'inactive' : 'active';
        $masterAccount->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => 'Status Master Account berhasil diupdate'
        ]);
    }

    /**
     * View assigned customers
     */
    public function customers(MasterAccount $masterAccount)
    {
        // $this->authorize('view', $masterAccount);

        $subscriptions = $masterAccount->subscriptions()
            ->with(['user', 'package'])
            ->latest()
            ->paginate(15);

        return view('admin.master-accounts.customers', compact('masterAccount', 'subscriptions'));
    }
}