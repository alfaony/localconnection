<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Software;
use App\Models\SoftwarePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SoftwarePackageController extends Controller
{
    /**
     * Display a listing of packages for a software
     */
    public function index(Request $request, Software $software)
    {
        $this->access('index', 'software_packages');

        $query = $software->packages();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $packages = $query->latest()->paginate(15);

        return view('admin.packages.index', compact('software', 'packages'));
    }

    /**
     * Show the form for creating a new package
     */
    public function create(Software $software)
    {
        $this->access('create', 'software_packages');

        return view('admin.packages.create', compact('software'));
    }

    /**
     * Store a newly created package
     */
    public function store(Request $request, Software $software)
    {
        $this->access('create', 'software_packages');

        $validated = $request->validate([
            'nama_paket' => 'required|string|max:255',
            'durasi_hari' => 'required|integer|min:1',
            'harga' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['software_id'] = $software->id;

        $package = SoftwarePackage::create($validated);

        return redirect()
            ->route('software.packages.index', $software)
            ->with('success', 'Package berhasil ditambahkan');
    }

    /**
     * Display the specified package
     */
    public function show(Software $software, SoftwarePackage $package)
    {
        $this->access('show', 'software_packages');

        $package->load(['subscriptions' => function($query) {
            $query->latest()->limit(10);
        }]);

        return view('admin.packages.show', compact('software', 'package'));
    }

    /**
     * Show the form for editing the specified package
     */
    public function edit(Software $software, SoftwarePackage $package)
    {
        $this->access('edit', 'software_packages');

        return view('admin.packages.edit', compact('software', 'package'));
    }

    /**
     * Update the specified package
     */
    public function update(Request $request, Software $software, SoftwarePackage $package)
    {
        $this->access('update', 'software_packages');

        $validated = $request->validate([
            'nama_paket' => 'required|string|max:255',
            'durasi_hari' => 'required|integer|min:1',
            'harga' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $package->update($validated);

        return redirect()
            ->route('software.packages.index', $software)
            ->with('success', 'Package berhasil diupdate');
    }

    /**
     * Remove the specified package
     */
    public function destroy(Software $software, SoftwarePackage $package)
    {
        $this->access('destroy', 'software_packages');

        // Check if package has active subscriptions
        $activeSubscriptions = $package->subscriptions()
            ->where('status', 'active')
            ->count();

        if ($activeSubscriptions > 0) {
            return redirect()
                ->back()
                ->with('error', 'Tidak dapat menghapus package yang memiliki subscription aktif');
        }

        $package->delete();

        return redirect()
            ->route('software.packages.index', $software)
            ->with('success', 'Package berhasil dihapus');
    }

    /**
     * Toggle package status
     */
    public function toggleStatus(Software $software, SoftwarePackage $package)
    {
        $this->access('toggleStatus', 'software_packages');

        $newStatus = $package->status === 'active' ? 'inactive' : 'active';
        $package->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => 'Status package berhasil diupdate'
        ]);
    }

    private function access($permssion, $methode)
    {
        return \App\Helpers\Access::can($permssion, $methode) ? true : abort(403);
    }
}