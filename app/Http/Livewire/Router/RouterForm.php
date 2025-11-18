<?php

namespace App\Http\Livewire\Router;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Services\RouterOSService;
use App\Jobs\{SyncRouterInventoryJob,RouterHealthCheckJob};

use App\Models\Router;
use App\Models\Pop;
use Illuminate\Validation\Rule;

class RouterForm extends Component
{
    public $mikrotik;
    public $mikrotikId;
    public $company_id;
    public $pop_id;
    public $name;
    public $user_id;
    public $mikrotik_host;
    public $mikrotik_port = '8728';
    public $mikrotik_username;
    public $mikrotik_password;
    public $mikrotik_ssl = false;
    public $mikrotik_active = false;

    // ✅ NEW: Connection check state
    public $hostChecked = false;
    public $hostAvailable = false;
    public $connectionTestResult = null;
    public $isTestingConnection = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'pop_id' => 'required|exists:pops,id',
            'mikrotik_host' => [
                'required',
                'string',
                'max:255',
                // ✅ Anti-duplicate: Unique host for company (except current router when editing)
                Rule::unique('routers', 'host')
                    ->where('company_id', Auth::user()->company_id)
                    ->whereNull('deleted_at') // ⬅️ Abaikan soft delete
                    ->ignore($this->mikrotikId),
            ],
            'mikrotik_port' => 'required|integer|min:1|max:65535',
            'mikrotik_username' => 'required|string|max:255',
            'mikrotik_password' => 'required|string',
            'mikrotik_ssl' => 'boolean',
        ];
    }

    protected $messages = [
        'mikrotik_host.unique' => 'Host ini sudah terdaftar untuk router lain di company Anda.',
        'mikrotik_host.required' => 'Host wajib diisi.',
        'mikrotik_port.required' => 'Port wajib diisi.',
        'mikrotik_port.integer' => 'Port harus berupa angka.',
        'mikrotik_port.min' => 'Port minimal 1.',
        'mikrotik_port.max' => 'Port maksimal 65535.',
        'mikrotik_username.required' => 'Username wajib diisi.',
        'mikrotik_password.required' => 'Password wajib diisi.',
        'name.required' => 'Nama router wajib diisi.',
        'pop_id.required' => 'POP wajib dipilih.',
    ];

    public function mount($mikrotik = null)
    {
        if ($mikrotik) {
            $mikrotik = Router::byCompany(auth()->user()->company_id)->find($mikrotik);
            $this->mikrotikId = $mikrotik->id;
            $this->name = $mikrotik->name;
            $this->pop_id = $mikrotik->pop_id;
            $this->mikrotik_host = $mikrotik->host;
            $this->mikrotik_port = $mikrotik->port;
            $this->mikrotik_username = $mikrotik->username;
            $this->mikrotik_password = $mikrotik->password;
            $this->mikrotik_ssl = $mikrotik->ssl;
            
            // Mark as checked since it's existing
            $this->hostChecked = true;
            $this->hostAvailable = true;
        } else {
            $this->company_id = Auth::user()->company_id;
            $this->user_id = Auth::user()->id;
        }
    }

    /**
     * ✅ Watch for host changes - reset check status
     */
    public function updatedMikrotikHost()
    {
        $this->hostChecked = false;
        $this->hostAvailable = false;
        $this->connectionTestResult = null;
        
        // Basic validation
        $this->validateOnly('mikrotik_host');
    }

    /**
     * ✅ Watch for port changes - reset check status
     */
    public function updatedMikrotikPort()
    {
        $this->hostChecked = false;
        $this->connectionTestResult = null;
        
        // Basic validation
        $this->validateOnly('mikrotik_port');
    }

    /**
     * ✅ Watch for username changes - reset check status
     */
    public function updatedMikrotikUsername()
    {
        $this->hostChecked = false;
        $this->connectionTestResult = null;
    }

    /**
     * ✅ Watch for password changes - reset check status
     */
    public function updatedMikrotikPassword()
    {
        $this->hostChecked = false;
        $this->connectionTestResult = null;
    }

    /**
     * ✅ Watch for SSL changes - reset check status
     */
    public function updatedMikrotikSsl()
    {
        $this->hostChecked = false;
        $this->connectionTestResult = null;
    }

    /**
     * ✅ NEW: Test connection to MikroTik
     */
    public function testConnection()
    {
        $this->isTestingConnection = true;
        $this->connectionTestResult = null;

        // Validate required fields first
        $this->validate([
            'mikrotik_host' => 'required',
            'mikrotik_port' => 'required|integer',
            'mikrotik_username' => 'required',
            'mikrotik_password' => 'required',
        ]);

        try {
            // Check if host already exists
            $existingRouter = Router::where('host', $this->mikrotik_host)
                ->where('company_id', Auth::user()->company_id)
                ->when($this->mikrotikId, function($q) {
                    return $q->where('id', '!=', $this->mikrotikId);
                })
                ->first();

            if ($existingRouter) {
                $this->hostChecked = true;
                $this->hostAvailable = false;
                $this->connectionTestResult = [
                    'success' => false,
                    'message' => 'Host sudah digunakan oleh router: ' . $existingRouter->name,
                    'type' => 'duplicate'
                ];
                $this->isTestingConnection = false;
                return;
            }

            // Test connection to MikroTik
            $ros = app(RouterOSService::class);
            
            $client = new \RouterOS\Client([
                'host' => $this->mikrotik_host,
                'user' => $this->mikrotik_username,
                'pass' => $this->mikrotik_password,
                'port' => (int)$this->mikrotik_port,
                'ssl' => (bool)$this->mikrotik_ssl,
                'timeout' => 5,
                'attempts' => 1,
            ]);

            // Try to get system identity
            $identity = $client->query(
                new \RouterOS\Query('/system/identity/print')
            )->read();

            $this->hostChecked = true;
            $this->hostAvailable = true;
            $this->connectionTestResult = [
                'success' => true,
                'message' => 'Koneksi berhasil! Router: ' . ($identity[0]['name'] ?? 'Unknown'),
                'type' => 'success',
                'identity' => $identity[0]['name'] ?? null,
            ];

        } catch (\RouterOS\Exceptions\ConnectException $e) {
            $this->hostChecked = true;
            $this->hostAvailable = false;
            $this->connectionTestResult = [
                'success' => false,
                'message' => 'Gagal terhubung: Tidak dapat mengakses router. Periksa host dan port.',
                'type' => 'connection_error',
                'error' => $e->getMessage()
            ];
        } catch (\RouterOS\Exceptions\ClientException $e) {
            $this->hostChecked = true;
            $this->hostAvailable = false;
            $this->connectionTestResult = [
                'success' => false,
                'message' => 'Gagal login: Username atau password salah.',
                'type' => 'auth_error',
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $this->hostChecked = true;
            $this->hostAvailable = false;
            $this->connectionTestResult = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'type' => 'general_error',
                'error' => $e->getMessage()
            ];
        } finally {
            $this->isTestingConnection = false;
        }
    }

    public function save()
    {
        // ✅ Validate all fields
        $this->validate();

        // ✅ CRITICAL: Require connection test before save
        if (!$this->hostChecked || !$this->hostAvailable) {
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'warning',
                'message' => 'Silakan test koneksi terlebih dahulu sebelum menyimpan!'
            ]);
            return;
        }

        $data = [
            'company_id' => Auth::user()->company_id,
            'user_id' => Auth::user()->id,
            'name' => $this->name,
            'pop_id' => $this->pop_id,
            'host' => $this->mikrotik_host,
            'port' => $this->mikrotik_port,
            'username' => $this->mikrotik_username,
            'password' => $this->mikrotik_password,
            'ssl' => $this->mikrotik_ssl,
            'active' => $this->mikrotik_active,
        ];

        if ($this->mikrotikId) {
            $data['status_active'] = Router::STATUS_UNKNOWN;
            
            Router::find($this->mikrotikId)->update($data);

            SyncRouterInventoryJob::dispatch($this->mikrotikId, true, true, true, true, true);
            dispatch(new RouterHealthCheckJob($this->mikrotikId));

            session()->flash('message', 'Mikrotik updated successfully.');
            return redirect()->route('router.show', $this->mikrotikId);
        } else {
            $mikrotik = Router::create($data);

            SyncRouterInventoryJob::dispatch($mikrotik->id, true, true, true, true, true);
            
            session()->flash('message', 'Mikrotik created successfully.');
            return redirect()->route('router.show', $mikrotik->id);
        }
    }

    public function render()
    {
        $pops = Pop::byCompany(Auth::user()->company_id)->get();

        return view('livewire.router.router-form', compact('pops'))
            ->extends('adminlte::page');
    }
}