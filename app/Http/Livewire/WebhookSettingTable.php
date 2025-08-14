<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;
use App\Models\WebhookSetting;
use Illuminate\Support\Facades\Auth;

class WebhookSettingTable extends Component
{
    use WithPagination;

    // Form fields
    public $app_name;
    public $selected_apps = [];
    public $url;
    public $token;
    
    // State variables
    public $editId = null;
    public $isEditMode = false;
    
    // Pagination
    protected $paginationTheme = 'bootstrap';
    public $perPage = 10;
    public $search = '';

    // Available apps for checkbox
    public $available_apps = [
        'used_laptops',
        // 'inventory',
        // 'sales',
        // 'customers',
        // 'orders'
    ];

    public function mount()
    {
        // Inisialisasi jika diperlukan
    }

    // Load settings from database
    public function getSettingsProperty()
    {
        return WebhookSetting::where('company_id', Auth::user()->company_id)
            ->when($this->search, function($query) {
                return $query->where(function($q) {
                    $q->where('app_name', 'like', '%'.$this->search.'%')
                      ->orWhere('url', 'like', '%'.$this->search.'%');
                });
            })
            ->paginate($this->perPage);
    }

    // Save or update setting
    public function save()
    {
        $this->validate([
            'app_name' => 'required|string|min:3',
            'selected_apps' => 'required|array|min:1',
            'url' => 'required|url',
            'token' => 'required|string|min:10'
        ]);

        $data = [
            'company_id' => Auth::user()->company_id,
            'app_name' => $this->app_name,
            'selected_apps' => $this->selected_apps,
            'url' => $this->url,
            'token' => $this->token
        ];

        if ($this->isEditMode) {
            // Update existing setting
            $setting = WebhookSetting::find($this->editId);
            if ($setting && $setting->company_id == Auth::user()->company_id) {
                $setting->update($data);
                session()->flash('message', 'Setting updated successfully!');
            } else {
                session()->flash('error', 'Setting not found or you do not have permission!');
            }
        } else {
            // Add new setting
            WebhookSetting::create($data);
            session()->flash('message', 'Setting created successfully!');
        }

        $this->resetForm();
    }

    // Edit setting
    public function edit($id)
    {
        $setting = WebhookSetting::where('company_id', Auth::user()->company_id)
            ->find($id);
        
        if ($setting) {
            $this->editId = $id;
            $this->app_name = $setting->app_name;
            $this->selected_apps = $setting->selected_apps;
            $this->url = $setting->url;
            $this->token = $setting->token;
            $this->isEditMode = true;
        } else {
            session()->flash('error', 'Setting not found!');
        }
    }

    // Delete setting
    public function delete($id)
    {
        $setting = WebhookSetting::where('company_id', Auth::user()->company_id)
            ->find($id);
        
        if ($setting) {
            $setting->delete();
            session()->flash('message', 'Setting deleted successfully!');
        } else {
            session()->flash('error', 'Setting not found!');
        }
        
        $this->resetForm();
    }

    // Reset form
    public function resetForm()
    {
        $this->reset([
            'app_name', 
            'selected_apps', 
            'url', 
            'token',
            'editId',
            'isEditMode'
        ]);
    }

    // Reset pagination saat melakukan pencarian
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    // Test connection
    public function testConnection()
    {
        $this->validate([
            'url' => 'required|url',
            'token' => 'required|string'
        ]);

        try {
            $response = Http::withToken($this->token)
                ->timeout(10)
                ->post($this->url);
            
            if ($response->successful()) {
                session()->flash('message', 'Connection successful! API responded.');
            } else {
                session()->flash('error', 'Connection failed. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.webhook-setting', [
            'settings' => $this->settings
        ])->extends('adminlte::page');
    }
}