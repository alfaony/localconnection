<?php

namespace App\Http\Livewire\InternetCustomerGroup;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\InternetCustomerGroup;
use App\Models\OpticalDistribution;

class InternetCustomerGroupIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search  = '';
    public $perPage = 10;

    // Form visibility
    public bool $showForm        = false;
    public bool $showDeleteModal = false;
    public bool $isEdit          = false;

    // Form fields
    public string  $name        = '';
    public string  $description = '';
    public int     $last_number = 0;
    public array   $selectedOdpIds = [];
    public ?string $editingId   = null;
    public ?string $deletingId  = null;

    protected function rules(): array
    {
        $uniqueRule = 'unique:internet_customer_groups,name,'
            . ($this->editingId ?? 'NULL')
            . ',id,company_id,' . Auth::user()->company_id
            . ',deleted_at,NULL';

        return [
            'name'             => ['required', 'string', 'max:100', $uniqueRule],
            'description'      => ['nullable', 'string', 'max:255'],
            'last_number'      => ['nullable', 'integer', 'min:0'],
            'selectedOdpIds'   => ['array'],
            'selectedOdpIds.*' => ['exists:optical_distributions,id'],
        ];
    }

    protected $messages = [
        'name.required' => 'Nama group wajib diisi.',
        'name.unique'   => 'Nama group sudah ada di perusahaan ini.',
        'name.max'      => 'Nama group maksimal 100 karakter.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // ── CREATE ───────────────────────────────────────────────────────────────

    public function create()
    {
        $this->isEdit         = false;
        $this->editingId      = null;
        $this->name           = '';
        $this->description    = '';
        $this->last_number    = 0;
        $this->selectedOdpIds = [];
        $this->resetErrorBag();
        $this->showForm = true;
        $this->dispatchBrowserEvent('odp-select2-init', ['ids' => []]);
    }

    public function store()
    {
        $this->validate();

        $group = InternetCustomerGroup::create([
            'company_id'  => Auth::user()->company_id,
            'name'        => trim($this->name),
            'description' => trim($this->description) ?: null,
            'last_number' => $this->last_number,
        ]);

        $group->odps()->sync($this->selectedOdpIds);

        $this->showForm       = false;
        $this->name           = '';
        $this->description    = '';
        $this->last_number    = 0;
        $this->selectedOdpIds = [];
        $this->resetErrorBag();

        $this->dispatchBrowserEvent('show-toast', [
            'type'    => 'success',
            'message' => 'Group berhasil ditambahkan.',
        ]);
    }

    // ── EDIT ─────────────────────────────────────────────────────────────────

    public function edit(string $id)
    {
        $group = InternetCustomerGroup::byCompany(Auth::user()->company_id)
            ->with('odps:id')
            ->findOrFail($id);

        $this->isEdit         = true;
        $this->editingId      = $group->id;
        $this->name           = $group->name;
        $this->description    = $group->description ?? '';
        $this->last_number    = (int) $group->last_number;
        $this->selectedOdpIds = $group->odps->pluck('id')->toArray();
        $this->resetErrorBag();
        $this->showForm = true;
        $this->dispatchBrowserEvent('odp-select2-init', ['ids' => $this->selectedOdpIds]);
    }

    public function update()
    {
        $this->validate();

        $group = InternetCustomerGroup::byCompany(Auth::user()->company_id)->findOrFail($this->editingId);
        $group->update([
            'name'        => trim($this->name),
            'description' => trim($this->description) ?: null,
            'last_number' => $this->last_number,
        ]);

        $group->odps()->sync($this->selectedOdpIds);

        $this->showForm       = false;
        $this->isEdit         = false;
        $this->editingId      = null;
        $this->name           = '';
        $this->description    = '';
        $this->last_number    = 0;
        $this->selectedOdpIds = [];
        $this->resetErrorBag();

        $this->dispatchBrowserEvent('show-toast', [
            'type'    => 'success',
            'message' => 'Group berhasil diperbarui.',
        ]);
    }

    public function cancel()
    {
        $this->showForm       = false;
        $this->isEdit         = false;
        $this->editingId      = null;
        $this->name           = '';
        $this->description    = '';
        $this->last_number    = 0;
        $this->selectedOdpIds = [];
        $this->resetErrorBag();
    }

    // ── DELETE ────────────────────────────────────────────────────────────────

    public function confirmDelete(string $id)
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->deletingId      = null;
        $this->showDeleteModal = false;
    }

    public function delete()
    {
        if (!$this->deletingId) return;

        $group = InternetCustomerGroup::byCompany(Auth::user()->company_id)->findOrFail($this->deletingId);
        $group->delete();

        $this->deletingId      = null;
        $this->showDeleteModal = false;

        $this->dispatchBrowserEvent('show-toast', [
            'type'    => 'success',
            'message' => 'Group berhasil dihapus.',
        ]);
    }

    public function render()
    {
        $groups = InternetCustomerGroup::byCompany(Auth::user()->company_id)
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
            )
            ->withCount('customers')
            ->with('odps:id,name')
            ->orderBy('name')
            ->paginate($this->perPage);

        $availableOdps = OpticalDistribution::byCompany(Auth::user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.internet-customer-group.internet-customer-group-index', compact('groups', 'availableOdps'))
            ->section('content')
            ->extends('adminlte::page');
    }
}
