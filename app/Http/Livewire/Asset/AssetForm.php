<?php

namespace App\Http\Livewire\Asset;

use Livewire\Component;
use App\Models\InternetAsset;
use Illuminate\Support\Facades\Auth;

class AssetForm extends Component
{
    public ?string $assetId = null;

    public string $name = '';
    public string $category = 'other';
    public string $brand = '';
    public string $model = '';
    public string $serial_number = '';
    public int    $quantity = 1;
    public string $unit_price = '';
    public string $purchase_date = '';
    public string $vendor = '';
    public int    $warranty_months = 0;
    public string $status = 'active';
    public string $damaged_at = '';
    public string $sold_at = '';
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'category'         => 'required|in:' . implode(',', array_keys(InternetAsset::categoryOptions())),
            'brand'            => 'nullable|string|max:100',
            'model'            => 'nullable|string|max:100',
            'serial_number'    => 'nullable|string|max:100',
            'quantity'         => 'required|integer|min:1',
            'unit_price'       => 'required|numeric|min:0',
            'purchase_date'    => 'required|date|before_or_equal:today',
            'vendor'           => 'nullable|string|max:150',
            'warranty_months'  => 'nullable|integer|min:0|max:120',
            'status'           => 'required|in:active,damaged,maintenance,sold',
            'damaged_at'       => 'nullable|date',
            'sold_at'          => 'nullable|date',
            'notes'            => 'nullable|string|max:1000',
        ];
    }

    protected $messages = [
        'name.required'          => 'Nama asset wajib diisi.',
        'unit_price.required'    => 'Harga satuan wajib diisi.',
        'purchase_date.required' => 'Tanggal beli wajib diisi.',
        'quantity.min'           => 'Jumlah minimal 1.',
    ];

    public function mount(?string $id = null)
    {
        $this->assetId = $id;
        if ($id) {
            $asset = InternetAsset::byCompany(Auth::user()->company_id)->findOrFail($id);
            $this->fill([
                'name'            => $asset->name,
                'category'        => $asset->category,
                'brand'           => $asset->brand ?? '',
                'model'           => $asset->model ?? '',
                'serial_number'   => $asset->serial_number ?? '',
                'quantity'        => $asset->quantity,
                'unit_price'      => $asset->unit_price,
                'purchase_date'   => $asset->purchase_date->format('Y-m-d'),
                'vendor'          => $asset->vendor ?? '',
                'warranty_months' => $asset->warranty_months,
                'status'          => $asset->status,
                'damaged_at'      => $asset->damaged_at?->format('Y-m-d') ?? '',
                'sold_at'         => $asset->sold_at?->format('Y-m-d') ?? '',
                'notes'           => $asset->notes ?? '',
            ]);
        } else {
            $this->purchase_date = now()->format('Y-m-d');
        }
    }

    public function updatedStatus($value)
    {
        if ($value === 'damaged' && !$this->damaged_at) {
            $this->damaged_at = now()->format('Y-m-d');
        }
        if ($value === 'sold' && !$this->sold_at) {
            $this->sold_at = now()->format('Y-m-d');
        }
        if ($value === 'active') {
            $this->damaged_at = '';
        }
    }

    public function save()
    {
        $data = $this->validate();

        // Normalize
        $data['unit_price'] = (float) str_replace(['.', ','], ['', '.'], $data['unit_price']);
        $data['damaged_at'] = $data['damaged_at'] ?: null;
        $data['sold_at']    = $data['sold_at'] ?: null;
        $data['warranty_months'] = $data['warranty_months'] ?? 0;
        $data['company_id'] = Auth::user()->company_id;

        if ($this->assetId) {
            InternetAsset::byCompany(Auth::user()->company_id)
                ->findOrFail($this->assetId)
                ->update($data);
            session()->flash('success', 'Asset berhasil diperbarui.');
        } else {
            InternetAsset::create($data);
            session()->flash('success', 'Asset berhasil ditambahkan.');
        }

        return redirect()->route('internet-asset.index');
    }

    public function render()
    {
        return view('livewire.asset.asset-form', [
            'categories' => InternetAsset::categoryOptions(),
        ])->extends('adminlte::page')->section('content');
    }
}
