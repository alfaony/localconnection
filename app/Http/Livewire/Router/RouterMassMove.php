<?php

namespace App\Http\Livewire\Router;

use App\Jobs\ProcessRouterMoveJob;
use App\Models\InternetCustomer;
use App\Models\Router;
use App\Schemas\ParamSchema;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RouterMassMove extends Component
{
    // Step: 1 = pilih router asal & customer, 2 = pilih router tujuan, 3 = konfirmasi, 4 = selesai
    public int $step = 1;

    public ?int $sourceRouterId = null;
    public ?int $targetRouterId = null;

    public array $selectedCustomers = [];
    public bool  $selectAll = false;

    public string $statusFilter   = 'all';
    public string $groupingFilter = 'all';
    public string $search = '';

    public bool   $skipOldRouter = false;

    public string $flashMessage = '';
    public string $flashType    = '';

    public array $resultLog = [];   // [{name, status, message}]
    public int   $dispatchedCount = 0;

    // ── Livewire hooks ────────────────────────────────────────────

    public function updatedSourceRouterId()
    {
        $this->selectedCustomers = [];
        $this->selectAll         = false;
        $this->targetRouterId    = null;
        $this->skipOldRouter     = false;
        $this->groupingFilter    = 'all';
        $this->step              = 1;

        // Auto-aktifkan skip jika router asal diketahui DOWN
        if ($this->sourceRouterId) {
            $router = Router::find($this->sourceRouterId);
            if ($router && $router->active_status === Router::STATUS_DOWN) {
                $this->skipOldRouter = true;
            }
        }
    }

    public function updatedSelectAll(bool $val)
    {
        $this->selectedCustomers = $val
            ? $this->getCustomerQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray()
            : [];
    }

    public function updatedSearch()
    {
        $this->selectedCustomers = [];
        $this->selectAll = false;
    }

    public function updatedStatusFilter()
    {
        $this->selectedCustomers = [];
        $this->selectAll = false;
    }

    public function updatedGroupingFilter()
    {
        $this->selectedCustomers = [];
        $this->selectAll = false;
    }

    // ── Navigation ────────────────────────────────────────────────

    public function goToStep2()
    {
        if (!$this->sourceRouterId) {
            $this->flash('Pilih router asal terlebih dahulu.', 'danger');
            return;
        }
        if (empty($this->selectedCustomers)) {
            $this->flash('Pilih minimal 1 pelanggan untuk dipindahkan.', 'danger');
            return;
        }
        $this->step = 2;
    }

    public function goToStep3()
    {
        if (!$this->targetRouterId) {
            $this->flash('Pilih router tujuan terlebih dahulu.', 'danger');
            return;
        }
        if ($this->targetRouterId == $this->sourceRouterId) {
            $this->flash('Router tujuan harus berbeda dari router asal.', 'danger');
            return;
        }
        $this->step = 3;
    }

    public function backToStep1()
    {
        $this->step = 1;
    }

    public function backToStep2()
    {
        $this->step = 2;
    }

    // ── Process ───────────────────────────────────────────────────

    public function process()
    {
        if (empty($this->selectedCustomers) || !$this->targetRouterId || !$this->sourceRouterId) {
            $this->flash('Data tidak lengkap.', 'danger');
            return;
        }

        $companyId = Auth::user()->company_id;
        $log       = [];
        $count     = 0;

        $customers = InternetCustomer::byCompany($companyId)
            ->whereIn('id', $this->selectedCustomers)
            ->where('router_id', $this->sourceRouterId)
            ->with('internetPackage')
            ->get();

        foreach ($customers as $customer) {
            try {
                ProcessRouterMoveJob::dispatch(
                    $customer->id,
                    $this->sourceRouterId,
                    $this->targetRouterId,
                    null, null, null,
                    $this->skipOldRouter,
                );
                $log[] = [
                    'name'    => $customer->name,
                    'code'    => $customer->code,
                    'status'  => 'queued',
                    'message' => 'Job antrian berhasil dibuat',
                ];
                $count++;
            } catch (\Throwable $e) {
                $log[] = [
                    'name'    => $customer->name,
                    'code'    => $customer->code,
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        $this->resultLog      = $log;
        $this->dispatchedCount = $count;
        $this->step            = 4;
    }

    public function reset_form()
    {
        $this->sourceRouterId    = null;
        $this->targetRouterId    = null;
        $this->selectedCustomers = [];
        $this->selectAll         = false;
        $this->skipOldRouter     = false;
        $this->statusFilter      = 'all';
        $this->groupingFilter    = 'all';
        $this->search            = '';
        $this->resultLog         = [];
        $this->dispatchedCount   = 0;
        $this->flashMessage      = '';
        $this->step              = 1;
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function flash(string $msg, string $type = 'success')
    {
        $this->flashMessage = $msg;
        $this->flashType    = $type;
    }

    private function getCustomerQuery()
    {
        $companyId = Auth::user()->company_id;

        $q = InternetCustomer::byCompany($companyId)
            ->where('router_id', $this->sourceRouterId)
            ->with('internetPackage');

        if ($this->statusFilter !== 'all') {
            $q->where('status', $this->statusFilter);
        }

        if ($this->groupingFilter === 'none') {
            $q->whereNull('grouping_id')->orWhere('grouping_id', '');
        } elseif ($this->groupingFilter !== 'all') {
            $q->where('grouping_id', $this->groupingFilter);
        }

        if ($this->search) {
            $s = '%' . $this->search . '%';
            $q->where(fn($qr) => $qr->where('name', 'like', $s)
                ->orWhere('username', 'like', $s)
                ->orWhere('code', 'like', $s));
        }

        return $q->orderBy('name');
    }

    // ── Render ────────────────────────────────────────────────────

    public function render()
    {
        $companyId = Auth::user()->company_id;

        $allRouters = Router::byCompany($companyId)
            ->withCount('internetCustomers')
            ->orderBy('name')
            ->get();

        $customers = collect();
        $groupingOptions = collect();
        if ($this->sourceRouterId && $this->step <= 2) {
            $customers = $this->getCustomerQuery()->get();
            $groupingOptions = InternetCustomer::byCompany($companyId)
                ->where('router_id', $this->sourceRouterId)
                ->whereNotNull('grouping_id')
                ->where('grouping_id', '!=', '')
                ->orderBy('grouping_id')
                ->pluck('grouping_id')
                ->unique()
                ->values();
        }

        $targetRouters = $allRouters->where('id', '!=', $this->sourceRouterId)->values();

        $sourceRouter = $this->sourceRouterId
            ? $allRouters->firstWhere('id', $this->sourceRouterId)
            : null;

        $targetRouter = $this->targetRouterId
            ? $allRouters->firstWhere('id', $this->targetRouterId)
            : null;

        $selectedCount = count($this->selectedCustomers);

        return view('livewire.router.router-mass-move', compact(
            'allRouters', 'customers', 'targetRouters',
            'sourceRouter', 'targetRouter', 'selectedCount', 'groupingOptions'
        ))->extends('adminlte::page')->section('content');
    }
}
