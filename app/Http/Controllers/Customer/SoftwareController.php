<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Software;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SoftwareController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display a listing of available softwares
     */
    public function index(Request $request)
    {
        $query = Software::with(['activePackages', 'availableMasterAccounts'])
            ->active();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('tipe_paket', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by company (if user wants to see specific company's offerings)
        if ($request->filled('company_id')) {
            $query->byCompany($request->company_id);
        }

        $softwares = $query->paginate(12);

        // Add slot availability info to each software
        $softwares->each(function($software) {
            $software->has_available_slots = $software->availableMasterAccounts->isNotEmpty();
        });

        return view('customer.softwares.index', compact('softwares'));
    }

    /**
     * Display the specified software detail
     */
    public function show($slug)
    {
        $software = Software::where('slug', $slug)
            ->with(['activePackages', 'availableMasterAccounts', 'company'])
            ->active()
            ->firstOrFail();

        // Check slot availability
        $hasAvailableSlots = $software->availableMasterAccounts->isNotEmpty();

        return view('customer.softwares.show', compact('software', 'hasAvailableSlots'));
    }
}