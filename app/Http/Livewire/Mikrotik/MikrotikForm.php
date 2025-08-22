<?php

namespace App\Http\Livewire\Mikrotik;

use App\Models\Router;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Services\MikrotikService;

class MikrotikForm extends Component
{
    public $mikrotikId;
    public $company_id;
    public $user_id;
    public $mikrotik_host;
    public $mikrotik_port = '8728';
    public $mikrotik_username;
    public $mikrotik_password;
    public $mikrotik_ssl = false;
    public $mikrotik_active = false;

    protected $rules = [
        'mikrotik_host' => 'required|string',
        'mikrotik_port' => 'required|string',
        'mikrotik_username' => 'required|string',
        'mikrotik_password' => 'required|string',
        'mikrotik_ssl' => 'boolean',
        // 'mikrotik_active' => 'boolean',
    ];

    public function mount($mikrotik = null)
    {
        if ($mikrotik) {
            $mikrotik = Router::byCompany(auth()->user()->company_id)->find($mikrotik);
            $this->mikrotikId = $mikrotik->id;
            $this->mikrotik_host = $mikrotik->host;
            $this->mikrotik_port = $mikrotik->port;
            $this->mikrotik_username = $mikrotik->username;
            $this->mikrotik_password = $mikrotik->password;
            $this->mikrotik_ssl = $mikrotik->ssl;
        } else {
            $this->company_id = Auth::user()->company_id;
            $this->user_id = Auth::user()->id;
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'company_id' => Auth::user()->company_id,
            'user_id' => Auth::user()->id,
            'host' => $this->mikrotik_host,
            'port' => $this->mikrotik_port,
            'username' => $this->mikrotik_username,
            'password' => $this->mikrotik_password,
            'ssl' => $this->mikrotik_ssl,
            'active' => $this->mikrotik_active,
        ];

        if ($this->mikrotikId) {
            Router::find($this->mikrotikId)->update($data);
            session()->flash('message', 'Mikrotik updated successfully.');
        } else {
            Router::create($data);
            session()->flash('message', 'Mikrotik created successfully.');
        }

        return redirect()->route('router.index');
    }

    public function render()
    {
        return view('livewire.mikrotik.mikrotik-form')
            ->extends('adminlte::page');
    }
}