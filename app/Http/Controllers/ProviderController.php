<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Provider;
use App\Models\ServiceType;

class ProviderController extends Controller
{
    public function index()
    {
        $providers = Provider::paginate(10); // Paginate results with 10 per page
        $serviceTypes = ServiceType::all();
        return view('provider.index', compact('providers','serviceTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_types' => 'required|array',
            'service_types.*' => 'exists:service_types,id',
            'factor_volumetric' => 'required|array',
            'factor_volumetric.*' => 'nullable|numeric|min:0',
        ]);
    
        $provider = Provider::create($request->only(['name', 'description', 'contact_info', 'email']));
    
        foreach ($validated['service_types'] as $serviceTypeId) {
            $factorVolumetric = $validated['factor_volumetric'][$serviceTypeId] ?? 0;
            $provider->serviceTypes()->attach($serviceTypeId, ['factor_volumetric' => $factorVolumetric]);
        }

        return redirect()->route('provider.index')->with('success', 'Provider berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $provider = Provider::findOrFail($id);
        $serviceTypes = ServiceType::all();
        $providers = Provider::paginate(10); // Paginate results with 10 per page

        return view('provider.index', compact('provider','providers','serviceTypes'));
    }
    public function update(Request $request, Provider $provider)
    {
        $validated = $request->validate([
            'service_types' => 'required|array',
            'service_types.*' => 'exists:service_types,id',
            'factor_volumetric' => 'required|array',
            'factor_volumetric.*' => 'nullable|numeric|min:0',
        ]);

    
        $provider->update($request->only(['name', 'description', 'contact_info', 'email']));
    
        // Sync service types and factor volumetric
        $syncData = [];
        foreach ($validated['service_types'] as $serviceTypeId) {
            $syncData[$serviceTypeId] = ['factor_volumetric' => $validated['factor_volumetric'][$serviceTypeId] ?? 0];
        }
        $provider->serviceTypes()->sync($syncData);

        return redirect()->route('provider.index')->with('success', 'Provider berhasil diperbarui.');
    }

    public function destroy(Provider $provider)
    {
        $provider->delete();

        return redirect()->route('provider.index')->with('success', 'Provider berhasil dihapus.');
    }
}