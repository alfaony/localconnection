<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerMonthlyReport;
use App\Models\PartnerTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerMonthlyReportController extends Controller
{
    /**
     * Show all months with report status
     */
    public function manage(Partner $partner, PartnerTarget $target)
    {
        $target->load(['targetValues.parameterType', 'targetValues.monthlyReports']);
        
        return view('partners.reports.manage', compact('partner', 'target'));
    }

    /**
     * Show form to create new report
     */
    public function create(Partner $partner, PartnerTarget $target)
    {
        $allMonths = config('partners.months');
        $target->load('targetValues.parameterType');
        
        // Get months that already have reports for this target year
        $reportedMonths = [];
        if ($target->targetValues->isNotEmpty()) {
            // Get the first target value to check which months have reports
            $firstTargetValue = $target->targetValues->first();
            $reportedMonths = PartnerMonthlyReport::where('partner_target_value_id', $firstTargetValue->id)
                ->where('year', $target->year)
                ->pluck('month')
                ->toArray();
        }
        
        // Filter out months that already have reports
        $months = array_diff_key($allMonths, array_flip($reportedMonths));

        return view('partners.reports.create', compact('partner', 'target', 'months'));
    }

    /**
     * Store new monthly report
     */
    public function store(Request $request, Partner $partner, PartnerTarget $target)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'reports' => 'required|array',
            'reports.*.target_value_id' => 'required|uuid|exists:partner_target_values,id',
            'reports.*.achievement_value' => 'required|numeric|min:0',
            'reports.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['reports'] as $reportData) {
                $existingReport = PartnerMonthlyReport::where('partner_target_value_id', $reportData['target_value_id'])
                    ->where('year', $target->year)
                    ->where('month', $validated['month'])
                    ->first();

                if ($existingReport) {
                    // Update existing report
                    $existingReport->update([
                        'achievement_value' => $reportData['achievement_value'],
                        'notes' => $reportData['notes'] ?? null,
                        'reported_by' => auth()->id() ?? '00000000-0000-0000-0000-000000000000',
                        'reported_at' => now(),
                    ]);
                } else {
                    // Create new report
                    PartnerMonthlyReport::create([
                        'user_id' => auth()->id(),
                        'partner_target_value_id' => $reportData['target_value_id'],
                        'year' => $target->year,
                        'month' => $validated['month'],
                        'achievement_value' => $reportData['achievement_value'],
                        'notes' => $reportData['notes'] ?? null,
                        'reported_by' => auth()->id() ?? '00000000-0000-0000-0000-000000000000',
                        'reported_at' => now(),
                    ]);
                }
            }

            DB::commit();

            $monthName = config('partners.months')[$validated['month']];
            
            return redirect()->route('partner.reports.manage', ['partner' => $partner, 'target' => $target])
                ->with('success', "Report for {$monthName} saved successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to save report: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show form to edit existing report
     */
    public function edit(Partner $partner, PartnerTarget $target, $month)
    {
        $months = config('partners.months');
        
        // Load target with values and their reports for specific month
        $target->load(['targetValues.parameterType', 'targetValues.monthlyReports' => function ($query) use ($month, $target) {
            $query->where('month', $month)->where('year', $target->year);
        }]);

        // Check if at least one report exists for this month
        $hasReport = false;
        foreach ($target->targetValues as $targetValue) {
            if ($targetValue->getMonthlyReport($month, $target->year)) {
                $hasReport = true;
                break;
            }
        }

        // If no report exists, redirect to create with month pre-selected
        if (!$hasReport) {
            return redirect()->route('partner.reports.create', ['partner' => $partner, 'target' => $target])
                ->with('info', 'No report found for ' . $months[$month] . '. Please create one.')
                ->with('month', $month);
        }

        return view('partners.reports.edit', compact('partner', 'target', 'month', 'months'));
    }

    /**
     * Update existing monthly report
     */
    public function update(Request $request, Partner $partner, PartnerTarget $target, $month)
    {
        $validated = $request->validate([
            'reports' => 'required|array',
            'reports.*.target_value_id' => 'required|uuid|exists:partner_target_values,id',
            'reports.*.achievement_value' => 'required|numeric|min:0',
            'reports.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['reports'] as $reportData) {
                $report = PartnerMonthlyReport::where('partner_target_value_id', $reportData['target_value_id'])
                    ->where('year', $target->year)
                    ->where('month', $month)
                    ->first();

                if ($report) {
                    // Update existing report
                    $report->update([
                        'achievement_value' => $reportData['achievement_value'],
                        'notes' => $reportData['notes'] ?? null,
                        'reported_by' => auth()->id() ?? '00000000-0000-0000-0000-000000000000',
                        'reported_at' => now(),
                    ]);
                } else {
                    // Create new report if doesn't exist (shouldn't happen in edit, but just in case)
                    PartnerMonthlyReport::create([
                        'user_id' => auth()->id(),
                        'partner_target_value_id' => $reportData['target_value_id'],
                        'year' => $target->year,
                        'month' => $month,
                        'achievement_value' => $reportData['achievement_value'],
                        'notes' => $reportData['notes'] ?? null,
                        'reported_by' => auth()->id() ?? '00000000-0000-0000-0000-000000000000',
                        'reported_at' => now(),
                    ]);
                }
            }

            DB::commit();

            $monthName = config('partners.months')[$month];
            
            return redirect()->route('partner.reports.manage', ['partner' => $partner, 'target' => $target])
                ->with('success', "Report for {$monthName} updated successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update report: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Delete monthly report
     */
    public function destroy(Partner $partner, PartnerTarget $target, $month)
    {
        DB::beginTransaction();
        try {
            // Delete all reports for this month
            foreach ($target->targetValues as $targetValue) {
                PartnerMonthlyReport::where('partner_target_value_id', $targetValue->id)
                    ->where('year', $target->year)
                    ->where('month', $month)
                    ->delete();
            }

            DB::commit();

            $monthName = config('partners.months')[$month];
            
            return redirect()->route('partner.reports.manage', ['partner' => $partner, 'target' => $target])
                ->with('success', "Report for {$monthName} deleted successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete report: ' . $e->getMessage()]);
        }
    }
}