<?php

namespace App\Http\Livewire\InternetCustomer;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use App\Models\InternetCustomerUserRegion;
use App\Models\Role;
use App\Schemas\RoleSchema;

class InternetCustomerUserRegionIndex extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public int $perPage = 10;

    // Form state
    public bool $showForm = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;
    public bool $showDeleteModal = false;

    // Form fields
    public ?string $user_id = null;
    public ?int $province_id = null;
    public ?int $city_id = null;
    public ?int $district_id = null;
    public ?int $subdistrict_id = null;

    // Cascading select options
    public array $cities = [];
    public array $districts = [];
    public array $subdistricts = [];

    protected function rules(): array
    {
        return [
            'user_id'        => ['required', 'exists:users,id'],
            'province_id'    => ['nullable', 'exists:provinces,id'],
            'city_id'        => ['nullable', 'exists:cities,id'],
            'district_id'    => ['nullable', 'exists:districts,id'],
            'subdistrict_id' => ['nullable', 'exists:subdistricts,id'],
        ];
    }

    protected $messages = [
        'user_id.required' => 'User wajib dipilih.',
        'user_id.exists'   => 'User tidak valid.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedProvinceId($value)
    {
        $this->city_id        = null;
        $this->district_id    = null;
        $this->subdistrict_id = null;
        $this->cities         = $value ? City::where('province_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
        $this->districts      = [];
        $this->subdistricts   = [];

        $this->dispatchBrowserEvent('region-cities-updated', ['cities' => $this->cities]);
        $this->dispatchBrowserEvent('region-districts-updated', ['districts' => []]);
        $this->dispatchBrowserEvent('region-subdistricts-updated', ['subdistricts' => []]);
    }

    public function updatedCityId($value)
    {
        $this->district_id    = null;
        $this->subdistrict_id = null;
        $this->districts      = $value ? District::where('city_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
        $this->subdistricts   = [];

        $this->dispatchBrowserEvent('region-districts-updated', ['districts' => $this->districts]);
        $this->dispatchBrowserEvent('region-subdistricts-updated', ['subdistricts' => []]);
    }

    public function updatedDistrictId($value)
    {
        $this->subdistrict_id = null;
        $this->subdistricts   = $value ? Subdistrict::where('district_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];

        $this->dispatchBrowserEvent('region-subdistricts-updated', ['subdistricts' => $this->subdistricts]);
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->dispatchBrowserEvent('region-form-opened');
    }

    public function store()
    {
        $this->validate();

        InternetCustomerUserRegion::create([
            'user_id'        => $this->user_id,
            'province_id'    => $this->province_id    ?: null,
            'city_id'        => $this->city_id        ?: null,
            'district_id'    => $this->district_id    ?: null,
            'subdistrict_id' => $this->subdistrict_id ?: null,
        ]);

        session()->flash('success', 'Region filter berhasil ditambahkan.');
        $this->resetForm();
        $this->showForm = false;
    }

    public function confirmDelete(string $id)
    {
        $this->deletingId    = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        InternetCustomerUserRegion::findOrFail($this->deletingId)->delete();

        session()->flash('success', 'Region filter berhasil dihapus.');
        $this->deletingId    = null;
        $this->showDeleteModal = false;
    }

    public function cancelDelete()
    {
        $this->deletingId    = null;
        $this->showDeleteModal = false;
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm()
    {
        $this->user_id        = null;
        $this->province_id    = null;
        $this->city_id        = null;
        $this->district_id    = null;
        $this->subdistrict_id = null;
        $this->cities         = [];
        $this->districts      = [];
        $this->subdistricts   = [];
        $this->editingId      = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $this->authorizeAccess();

        $eligibleRoleIds = Role::whereHas('permissions', function ($q) {
            $q->where('table', 'internet_customers')
              ->whereIn('method', ['as_finance', 'as_marketing', 'as_technician',"as_manager"]);
        })->pluck('id');

        $selectableUsers = User::whereIn('role_id', $eligibleRoleIds)
            ->byCompany(Auth::user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name', 'role_id']);

        $regions = InternetCustomerUserRegion::with([
                'user:id,name,role_id',
                'user.role:id,name',
                'province:id,name',
                'city:id,name',
                'district:id,name',
                'subdistrict:id,name',
            ])
            ->whereHas('user', function ($q) {
                $q->byCompany(Auth::user()->company_id);
            })
            ->when($this->search, function ($q) {
                $q->whereHas('user', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $provinces = Province::whereHas('provinceCoverages')->orderBy('name')->get(['id', 'name']);

        return view('livewire.internet-customer.internet-customer-user-region-index', [
            'regions'         => $regions,
            'provinces'       => $provinces,
            'selectableUsers' => $selectableUsers,
        ])->extends('adminlte::page');
    }

    private function authorizeAccess()
    {
        $role = Auth::user()->role->name ?? '';
        $allowed = [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::MANAGER];
        if (!in_array($role, $allowed)) {
            abort(403);
        }
    }
}
