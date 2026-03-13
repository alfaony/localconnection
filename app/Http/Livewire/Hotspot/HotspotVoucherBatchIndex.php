<?php

namespace App\Http\Livewire\Hotspot;

use App\Models\HotspotVoucherBatch;
use App\Models\InternetPackage;
use App\Models\HotspotServer;
use App\Jobs\GenerateVoucherBatchJob;
use Livewire\Component;
use Livewire\WithPagination;

class HotspotVoucherBatchIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Form generate batch
    public bool   $showForm = false;
    public string $hotspot_server_id = '';
    public string $internet_package_id = '';
    public int    $quantity = 10;
    public string $prefix = 'VOC';

    protected function rules(): array
    {
        return [
            'hotspot_server_id'  => 'required|exists:hotspot_servers,id',
            'internet_package_id' => 'required|exists:internet_packages,id',
            'quantity'            => 'required|integer|min:1|max:500',
            'prefix'              => 'required|string|max:10',
        ];
    }

    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
    }

    public function generate(): void
    {
        $this->validate();

        $batch = HotspotVoucherBatch::create([
            'company_id'          => auth()->user()->company_id,
            'hotspot_server_id'   => $this->hotspot_server_id,
            'internet_package_id' => $this->internet_package_id,
            'quantity'            => $this->quantity,
            'prefix'              => strtoupper($this->prefix),
            'generated_by'        => auth()->id(),
        ]);

        GenerateVoucherBatchJob::dispatch($batch->id);

        $this->showForm = false;
        $this->resetForm();
        session()->flash('message', "Batch {$this->quantity} voucher sedang di-generate...");
    }

    private function resetForm(): void
    {
        $this->hotspot_server_id   = '';
        $this->internet_package_id = '';
        $this->quantity            = 10;
        $this->prefix              = 'VOC';
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $batches  = HotspotVoucherBatch::with(['internetPackage', 'hotspotServer', 'generatedBy'])
            ->withCount([
                'vouchers as used_count'   => fn($q) => $q->whereIn('status', ['active', 'expired']),
                'vouchers as unused_count' => fn($q) => $q->where('status', 'unused'),
            ])
            ->where('company_id', $companyId)
            ->latest()
            ->paginate(15);

        $servers  = HotspotServer::whereHas('router', fn($q) => $q->where('company_id', $companyId))->get();
        $profiles = InternetPackage::where('company_id', $companyId)
            ->where('access_type', 'hotspot')
            ->where('is_active', true)
            ->get();

        return view('livewire.hotspot-voucher.batch-index', compact('batches', 'servers', 'profiles'))
            ->extends('adminlte::page');
    }
}
