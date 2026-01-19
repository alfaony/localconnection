<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $query = Partner::query();


        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('partner_type')) {
            $query->where('partner_type', $request->partner_type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('industry', 'like', '%' . $request->search . '%');
            });
        }

        $partners = $query->byCompany(Auth::user()->company_id)->orderBy('name')->paginate(15);

        return view('partners.index', compact('partners'));
    }

    public function create()
    {
        $partnerTypes = config('partners.partner_types');
        $statuses = config('partners.partner_status');
        $certificationLevels = config('partners.certification_levels');
        $users = User::byCompany(Auth::user()->company_id)->orderBy('name')->get();

        return view('partners.create', compact('partnerTypes', 'statuses', 'certificationLevels', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pic_user_id' => 'required|uuid',
            'name' => 'required|string|max:255',
            'partner_type' => 'required|string',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'status' => 'required|in:active,inactive,suspended',
            'is_certified' => 'boolean',
            'certification_level' => 'nullable|string',
            'certified_at' => 'nullable|date',
            'partnership_started_at' => 'nullable|date',
            'certification_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $validated['is_certified'] = $request->boolean('is_certified');
        $validated['company_id'] = Auth::user()->company_id;

        // Handle file upload to S3
        if ($request->hasFile('certification_file')) {
            $file = $request->file('certification_file');
            $path = $file->store('partners/certifications', 's3');
            $validated['certification_file'] = $path;
        }

        $partner = Partner::create($validated);

        return redirect()->route('partner.show', $partner)
            ->with('success', 'Partner created successfully!');
    }

    public function show(Partner $partner)
    {
        $partner->load(['targets.targetValues.parameterType', 'targets.targetValues.monthlyReports']);
        
        return view('partners.show', compact('partner'));
    }

    public function edit(Partner $partner)
    {
        $partnerTypes = config('partners.partner_types');
        $statuses = config('partners.partner_status');
        $certificationLevels = config('partners.certification_levels');
        $users = User::byCompany(Auth::user()->company_id)->orderBy('name')->get();

        return view('partners.edit', compact('partner', 'partnerTypes', 'statuses', 'certificationLevels', 'users'));
    }

    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'pic_user_id' => 'required|uuid',
            'name' => 'required|string|max:255',
            'partner_type' => 'required|string',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'status' => 'required|in:active,inactive,suspended',
            'is_certified' => 'boolean',
            'certification_level' => 'nullable|string',
            'certified_at' => 'nullable|date',
            'partnership_started_at' => 'nullable|date',
            'certification_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $validated['is_certified'] = $request->boolean('is_certified');
        $validated['company_id'] = Auth::user()->company_id;

        // Handle file upload to S3
        if ($request->hasFile('certification_file')) {
            // Delete old file if exists
            if ($partner->certification_file) {
                \Storage::disk('s3')->delete($partner->certification_file);
            }
            
            $file = $request->file('certification_file');
            $path = $file->store('partners/certifications', 's3');
            $validated['certification_file'] = $path;
        }

        $partner->update($validated);

        return redirect()->route('partner.show', $partner)
            ->with('success', 'Partner updated successfully!');
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();

        return redirect()->route('partner.index')
            ->with('success', 'Partner deleted successfully!');
    }
}