<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerParameterType;
use App\Models\PartnerTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PartnerTargetController extends Controller
{
    public function create(Partner $partner)
    {
        // Get all years from current year to 5 years ahead
        $allYears = range(date('Y'), date('Y') + 5);
        
        // Get years that already have targets for this partner
        $existingYears = $partner->targets()->pluck('year')->toArray();
        
        // Filter out years that already have targets
        $years = array_diff($allYears, $existingYears);
        
        $parameters = PartnerParameterType::byCompany(Auth::user()->company_id)->active()->ordered()->get();
        $targetStatuses = config('partners.target_status');

        return view('partners.targets.create', compact('partner', 'years', 'parameters', 'targetStatuses'));
    }

    public function store(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'status' => 'required|in:draft,active,completed',
            'notes' => 'nullable|string',
            'targets' => 'required|array',
            'targets.*.parameter_type_id' => 'required|uuid|exists:partner_parameter_types,id',
            'targets.*.target_value' => 'required|numeric|min:0',
            'targets.*.description' => 'nullable|string',
        ]);

        $existingTarget = $partner->targets()->where('year', $validated['year'])->first();
        if ($existingTarget) {
            return back()->withErrors(['year' => 'Target for this year already exists.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $target = $partner->targets()->create([
                'year' => $validated['year'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id() ?? '00000000-0000-0000-0000-000000000000',
            ]);

            foreach ($validated['targets'] as $targetData) {
                $target->targetValues()->create([
                    'parameter_type_id' => $targetData['parameter_type_id'],
                    'target_value' => $targetData['target_value'],
                    'description' => $targetData['description'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('partner.show', $partner)
                ->with('success', 'Target created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create target: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Partner $partner, PartnerTarget $target)
    {
        // Only show the current year being edited (no year change allowed)
        $years = [$target->year];
        
        $parameters = PartnerParameterType::byCompany(Auth::user()->company_id)->active()->ordered()->get();
        $targetStatuses = config('partners.target_status');
        $target->load('targetValues.parameterType');
        
        // Check if there are any monthly reports
        $hasReports = false;
        foreach ($target->targetValues as $targetValue) {
            if ($targetValue->monthlyReports()->count() > 0) {
                $hasReports = true;
                break;
            }
        }

        return view('partners.targets.edit', compact('partner', 'target', 'years', 'parameters', 'targetStatuses', 'hasReports'));
    }

    public function update(Request $request, Partner $partner, PartnerTarget $target)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,active,completed',
            'notes' => 'nullable|string',
            'targets' => 'required|array',
            'targets.*.parameter_type_id' => 'required|uuid|exists:partner_parameter_types,id',
            'targets.*.target_value' => 'required|numeric|min:0',
            'targets.*.description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Year cannot be changed in edit mode
            $target->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'],
            ]);

            // =====================================================
            // SOLUTION: UPDATE INSTEAD OF DELETE
            // This preserves monthly reports!
            // =====================================================
            
            $existingTargetValues = $target->targetValues->keyBy('parameter_type_id');
            $newParameterIds = collect($validated['targets'])->pluck('parameter_type_id');

            // Update or create target values
            foreach ($validated['targets'] as $targetData) {
                $parameterId = $targetData['parameter_type_id'];
                
                if ($existingTargetValues->has($parameterId)) {
                    // UPDATE existing target value
                    $existingTargetValues[$parameterId]->update([
                        'target_value' => $targetData['target_value'],
                        'description' => $targetData['description'] ?? null,
                    ]);
                } else {
                    // CREATE new target value
                    $target->targetValues()->create([
                        'parameter_type_id' => $parameterId,
                        'target_value' => $targetData['target_value'],
                        'description' => $targetData['description'] ?? null,
                    ]);
                }
            }

            // SOFT DELETE removed parameters (that have reports)
            // HARD DELETE removed parameters (that don't have reports)
            $removedParameterIds = $existingTargetValues->keys()->diff($newParameterIds);
            foreach ($removedParameterIds as $removedId) {
                $targetValue = $existingTargetValues[$removedId];
                
                if ($targetValue->monthlyReports()->count() > 0) {
                    // Has reports - use soft delete
                    $targetValue->delete(); // Soft delete
                } else {
                    // No reports - can hard delete
                    $targetValue->forceDelete();
                }
            }

            DB::commit();

            return redirect()->route('partner.show', $partner)
                ->with('success', 'Target updated successfully! Monthly reports have been preserved.');

        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update target: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Partner $partner, PartnerTarget $target)
    {
        // Check if there are any monthly reports
        $hasReports = false;
        foreach ($target->targetValues as $targetValue) {
            if ($targetValue->monthlyReports()->count() > 0) {
                $hasReports = true;
                break;
            }
        }

        if ($hasReports) {
            return back()->withErrors([
                'error' => 'Cannot delete target because it has monthly reports. Please delete the reports first.'
            ]);
        }

        $target->delete();

        return redirect()->route('partner.show', $partner)
            ->with('success', 'Target deleted successfully!');
    }
}