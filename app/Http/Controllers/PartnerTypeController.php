<?php

namespace App\Http\Controllers;

use App\Models\PartnerType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerTypeController extends Controller
{
    public function index()
    {
        $partnerTypes = PartnerType::byCompany(Auth::user()->company_id)
            ->orderBy('name')
            ->paginate(15);
            
        return view('partner_types.index', compact('partnerTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        $validated['company_id'] = Auth::user()->company_id;
        $validated['is_active'] = $request->has('is_active');
        
        // Ensure name is unique for this company
        $exists = PartnerType::byCompany(Auth::user()->company_id)
            ->where('name', $validated['name'])
            ->exists();
            
        if ($exists) {
            return redirect()->route('partner-type.index')->with('error', 'Partner Type with this name already exists.');
        }

        PartnerType::create($validated);

        return redirect()->route('partner-type.index')->with('success', 'Partner Type created successfully.');
    }

    public function update(Request $request, PartnerType $partnerType)
    {
        if ($partnerType->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        
        // Ensure name is unique for this company
        $exists = PartnerType::byCompany(Auth::user()->company_id)
            ->where('name', $validated['name'])
            ->where('id', '!=', $partnerType->id)
            ->exists();
            
        if ($exists) {
            return redirect()->route('partner-type.index')->with('error', 'Partner Type with this name already exists.');
        }

        $partnerType->update($validated);

        return redirect()->route('partner-type.index')->with('success', 'Partner Type updated successfully.');
    }

    public function destroy(PartnerType $partnerType)
    {
        if ($partnerType->company_id !== Auth::user()->company_id) {
            abort(403);
        }
        
        // Check if types are in use
        if ($partnerType->partners()->count() > 0) {
            return redirect()->route('partner-type.index')->with('error', 'Cannot delete Partner Type. It is currently in use.');
        }

        $partnerType->delete();

        return redirect()->route('partner-type.index')->with('success', 'Partner Type deleted successfully.');
    }
}
