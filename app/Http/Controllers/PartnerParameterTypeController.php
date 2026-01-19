<?php

namespace App\Http\Controllers;

use App\Models\PartnerParameterType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PartnerParameterTypeController extends Controller
{
    public function index()
    {
        $parameters = PartnerParameterType::byCompany(Auth::user()->company_id)->orderBy('sort_order')->orderBy('name')->paginate(15);
        
        return view('partners.parameter-types.index', compact('parameters'));
    }

    public function create(PartnerParameterType $parameterType = null)
    {
        // If $parameterType is null, it's create mode
        // If $parameterType exists, it's edit mode
        $isEdit = $parameterType && $parameterType->exists;
        
        return view('partners.parameter-types.createOrEdit', compact('parameterType', 'isEdit'));
    }

    public function edit($id)
    {
        // If $parameterType is null, it's create mode
        // If $parameterType exists, it's edit mode
        $parameterType = PartnerParameterType::find($id);
        $isEdit = $parameterType && $parameterType->exists;
        
        return view('partners.parameter-types.createOrEdit', compact('parameterType', 'isEdit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('partner_parameter_types', 'name')
                    ->where('company_id', Auth::user()->company_id)
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('partner_parameter_types', 'code')
                    ->where('company_id', Auth::user()->company_id)
            ],
            'unit' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['company_id'] = Auth::user()->company_id;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        PartnerParameterType::create($validated);

        return redirect()->route('partner-parameter-type.index')
            ->with('success', 'Parameter type created successfully!');
    }

    public function update(Request $request, $id)
    {
        $parameterType = PartnerParameterType::findOrFail($id);
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('partner_parameter_types', 'name')
                    ->ignore($parameterType->id)
                    ->where('company_id', Auth::user()->company_id)
            ],
            // Code is readonly in edit mode, so we don't validate it
            'unit' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // Don't update the code field as it's readonly
        $parameterType->update($validated);

        return redirect()->route('partner-parameter-type.index')
            ->with('success', 'Parameter type updated successfully!');
    }

    public function destroy(PartnerParameterType $parameterType)
    {
        // Check if parameter type is being used
        if ($parameterType->targetValues()->count() > 0) {
            return redirect()->route('partner-parameter-type.index')
                ->with('error', 'Cannot delete parameter type that is being used in targets!');
        }

        $parameterType->delete();

        return redirect()->route('partner-parameter-type.index')
            ->with('success', 'Parameter type deleted successfully!');
    }

    public function toggleActive(PartnerParameterType $parameterType)
    {
        $parameterType->update([
            'is_active' => !$parameterType->is_active
        ]);

        return redirect()->route('partner-parameter-type.index')
            ->with('success', 'Parameter type status updated!');
    }
}