<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Software;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SoftwareController extends Controller
{
    /**
     * Display a listing of softwares
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $query = Software::byCompany($companyId)
            ->with(['packages', 'masterAccounts']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('tipe_paket', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $softwares = $query->latest()->paginate(15);

        return view('admin.softwares.index', compact('softwares'));
    }

    /**
     * Show the form for creating a new software
     */
    public function create()
    {
        return view('admin.softwares.create');
    }

    /**
     * Store a newly created software
     */
    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tipe_paket' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        // Generate slug
        $validated['slug'] = Str::slug($validated['nama'] . '-' . $validated['tipe_paket']);
        
        // Check if slug already exists for this company
        $count = Software::byCompany($companyId)
            ->where('slug', $validated['slug'])
            ->count();
        
        if ($count > 0) {
            $validated['slug'] .= '-' . Str::random(6);
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . Str::slug($validated['nama']) . '.' . $logo->extension();
            $logo->storeAs('public/softwares', $logoName);
            $validated['logo'] = 'softwares/' . $logoName;
        }

        $validated['company_id'] = $companyId;

        $software = Software::create($validated);

        return redirect()
            ->route('admin.softwares.index')
            ->with('success', 'Software berhasil ditambahkan');
    }

    /**
     * Display the specified software
     */
    public function show(Software $software)
    {

        $software->load(['packages', 'masterAccounts.activeSubscriptions']);

        return view('admin.softwares.show', compact('software'));
    }

    /**
     * Show the form for editing the specified software
     */
    public function edit(Software $software)
    {

        return view('admin.softwares.edit', compact('software'));
    }

    /**
     * Update the specified software
     */
    public function update(Request $request, Software $software)
    {

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tipe_paket' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        // Update slug if nama or tipe_paket changed
        if ($software->nama !== $validated['nama'] || $software->tipe_paket !== $validated['tipe_paket']) {
            $newSlug = Str::slug($validated['nama'] . '-' . $validated['tipe_paket']);
            
            // Check if new slug already exists (excluding current software)
            $count = Software::byCompany($software->company_id)
                ->where('slug', $newSlug)
                ->where('id', '!=', $software->id)
                ->count();
            
            if ($count > 0) {
                $newSlug .= '-' . Str::random(6);
            }
            
            $validated['slug'] = $newSlug;
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($software->logo) {
                \Storage::delete('public/' . $software->logo);
            }

            $logo = $request->file('logo');
            $logoName = time() . '_' . Str::slug($validated['nama']) . '.' . $logo->extension();
            // $logo->storeAs('public/softwares', $logoName);
            $coba = Storage::putFileAs('public/softwares', $logo, $logoName);
            $check = Storage::disk('s3')->putFileAs('public/softwares', $logo, $logoName);

            // dd($check);
            $validated['logo'] = 'public/softwares/' . $logoName;
        }

        $software->update($validated);

        return redirect()
            ->route('admin.softwares.index')
            ->with('success', 'Software berhasil diupdate');
    }

    /**
     * Remove the specified software
     */
    public function destroy(Software $software)
    {

        // Check if software has active subscriptions
        $activeSubscriptions = $software->masterAccounts()
            ->withCount(['activeSubscriptions'])
            ->get()
            ->sum('active_subscriptions_count');

        if ($activeSubscriptions > 0) {
            return redirect()
                ->back()
                ->with('error', 'Tidak dapat menghapus software yang memiliki subscription aktif');
        }

        // Delete logo if exists
        if ($software->logo) {
            \Storage::delete('public/' . $software->logo);
        }

        $software->delete();

        return redirect()
            ->route('admin.softwares.index')
            ->with('success', 'Software berhasil dihapus');
    }

    /**
     * Toggle software status
     */
    public function toggleStatus(Software $software)
    {

        $newStatus = $software->status === 'active' ? 'inactive' : 'active';
        $software->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => 'Status software berhasil diupdate'
        ]);
    }
}