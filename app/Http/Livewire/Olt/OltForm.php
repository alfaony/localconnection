<?php

namespace App\Http\Livewire\Olt;

use App\Helpers\Access;
use App\Jobs\PollOltJob;
use App\Models\Olt;
use App\Models\Pop;
use App\Services\Olt\OltMonitoringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

class OltForm extends Component
{
    public $olt = null;
    public $oltId = null;
    public $popId = '';
    public $name = '';
    public $vendor = 'hioso';
    public $model = '';
    public $host = '';
    public $port = 161;
    public $snmpVersion = '2c';
    public $community = '';
    public $snmpUsername = '';
    public $securityLevel = 'authPriv';
    public $authProtocol = 'SHA';
    public $authPassword = '';
    public $privProtocol = 'AES';
    public $privPassword = '';
    public $pollingEnabled = true;
    public $pollingInterval = 5;
    public $oidMapJson = '{}';
    public $connectionResult = null;

    public function mount($olt = null): void
    {
        if (!$olt) {
            return;
        }

        $this->olt = Olt::byCompany(Auth::user()->company_id)->findOrFail((int) $olt);
        $this->oltId = $this->olt->id;
        $this->popId = $this->olt->pop_id;
        $this->name = $this->olt->name;
        $this->vendor = $this->olt->vendor;
        $this->model = $this->olt->model ?: '';
        $this->host = $this->olt->host;
        $this->port = $this->olt->port;
        $this->snmpVersion = $this->olt->snmp_version;
        $this->snmpUsername = $this->olt->snmp_username ?: '';
        $this->securityLevel = $this->olt->snmp_security_level;
        $this->authProtocol = $this->olt->snmp_auth_protocol ?: 'SHA';
        $this->privProtocol = $this->olt->snmp_priv_protocol ?: 'AES';
        $this->pollingEnabled = $this->olt->polling_enabled;
        $this->pollingInterval = $this->olt->polling_interval;
        $this->oidMapJson = json_encode($this->olt->oid_map ?: new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function rules(): array
    {
        $rules = [
            'popId' => [
                'required',
                Rule::exists('pops', 'id')->where('company_id', Auth::user()->company_id),
            ],
            'name' => ['required', 'string', 'max:191'],
            'vendor' => ['required', Rule::in(['hioso', 'hsgq', 'generic'])],
            'model' => ['nullable', 'string', 'max:191'],
            'host' => [
                'required',
                'string',
                'max:191',
                'regex:/^[A-Za-z0-9][A-Za-z0-9.\\-:]*$/',
                Rule::unique('olts', 'host')
                    ->where('company_id', Auth::user()->company_id)
                    ->whereNull('deleted_at')
                    ->ignore($this->oltId),
            ],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'snmpVersion' => ['required', Rule::in(['1', '2c', '3'])],
            'community' => ['nullable', 'string', 'max:191'],
            'snmpUsername' => ['nullable', 'string', 'max:191'],
            'securityLevel' => ['nullable', Rule::in(['noAuthNoPriv', 'authNoPriv', 'authPriv'])],
            'authProtocol' => ['nullable', Rule::in(['MD5', 'SHA'])],
            'authPassword' => ['nullable', 'string', 'min:8'],
            'privProtocol' => ['nullable', Rule::in(['DES', 'AES'])],
            'privPassword' => ['nullable', 'string', 'min:8'],
            'pollingEnabled' => ['boolean'],
            'pollingInterval' => ['required', 'integer', 'min:1', 'max:1440'],
            'oidMapJson' => ['nullable', 'json'],
        ];

        if ($this->snmpVersion === '3') {
            $rules['snmpUsername'][0] = 'required';
            $rules['securityLevel'] = ['required', Rule::in(['noAuthNoPriv', 'authNoPriv', 'authPriv'])];

            if (
                in_array($this->securityLevel, ['authNoPriv', 'authPriv'], true)
                && (!$this->olt || (!$this->authPassword && !$this->olt->snmp_auth_password))
            ) {
                $rules['authPassword'][0] = 'required';
            }
            if (
                $this->securityLevel === 'authPriv'
                && (!$this->olt || (!$this->privPassword && !$this->olt->snmp_priv_password))
            ) {
                $rules['privPassword'][0] = 'required';
            }
        } elseif (!$this->olt || (!$this->community && !$this->olt->snmp_community)) {
            $rules['community'][0] = 'required';
        }

        return $rules;
    }

    protected $messages = [
        'host.regex' => 'Host hanya boleh berupa alamat IP atau hostname tanpa http://.',
        'host.unique' => 'Host OLT ini sudah terdaftar.',
        'oidMapJson.json' => 'Mapping OID harus berupa JSON yang valid.',
    ];

    public function testConnection(OltMonitoringService $monitoring): void
    {
        abort_unless(Access::can($this->oltId ? 'edit' : 'create', 'olts'), 403);

        $this->validate();
        $this->connectionResult = null;

        try {
            $health = $monitoring->testConnection($this->connectionCandidate());
            $this->connectionResult = [
                'success' => true,
                'message' => 'Koneksi SNMP berhasil.',
                'description' => $health['system_description'],
            ];
        } catch (Throwable $exception) {
            $this->connectionResult = [
                'success' => false,
                'message' => mb_substr($exception->getMessage(), 0, 500),
            ];
        }
    }

    public function save()
    {
        abort_unless(Access::can($this->oltId ? 'update' : 'store', 'olts'), 403);

        $this->validate();

        $data = $this->deviceData();
        $data['company_id'] = Auth::user()->company_id;
        $data['oid_map'] = json_decode($this->oidMapJson ?: '{}', true);

        if ($this->community !== '') {
            $data['snmp_community'] = $this->community;
        }
        if ($this->authPassword !== '') {
            $data['snmp_auth_password'] = $this->authPassword;
        }
        if ($this->privPassword !== '') {
            $data['snmp_priv_password'] = $this->privPassword;
        }

        if ($this->olt) {
            $this->olt->update($data);
            $olt = $this->olt;
            $message = 'OLT berhasil diperbarui.';
        } else {
            $data['created_by'] = Auth::id();
            $olt = Olt::create($data);
            $message = 'OLT berhasil ditambahkan.';
        }

        PollOltJob::dispatch($olt->id)->onQueue(config('olt.queue', 'default'))->afterResponse();
        session()->flash('message', $message.' Sinkronisasi awal dijadwalkan.');

        return redirect()->route('olt.show', $olt->id);
    }

    private function connectionCandidate(): Olt
    {
        $candidate = $this->olt ? $this->olt->replicate() : new Olt();
        $candidate->fill($this->deviceData());
        $candidate->oid_map = json_decode($this->oidMapJson ?: '{}', true);

        if ($this->community !== '') {
            $candidate->snmp_community = $this->community;
        } elseif ($this->olt) {
            $candidate->snmp_community = $this->olt->snmp_community;
        }

        if ($this->authPassword !== '') {
            $candidate->snmp_auth_password = $this->authPassword;
        } elseif ($this->olt) {
            $candidate->snmp_auth_password = $this->olt->snmp_auth_password;
        }

        if ($this->privPassword !== '') {
            $candidate->snmp_priv_password = $this->privPassword;
        } elseif ($this->olt) {
            $candidate->snmp_priv_password = $this->olt->snmp_priv_password;
        }

        return $candidate;
    }

    private function deviceData(): array
    {
        return [
            'pop_id' => $this->popId,
            'name' => $this->name,
            'vendor' => $this->vendor,
            'model' => $this->model ?: null,
            'host' => $this->host,
            'port' => $this->port,
            'snmp_version' => $this->snmpVersion,
            'snmp_username' => $this->snmpUsername ?: null,
            'snmp_security_level' => $this->securityLevel,
            'snmp_auth_protocol' => $this->authProtocol ?: null,
            'snmp_priv_protocol' => $this->privProtocol ?: null,
            'polling_enabled' => $this->pollingEnabled,
            'polling_interval' => $this->pollingInterval,
            'status' => Olt::STATUS_UNKNOWN,
        ];
    }

    public function render()
    {
        $pops = Pop::byCompany(Auth::user()->company_id)->orderBy('name')->get(['id', 'name']);

        return view('livewire.olt.olt-form', compact('pops'))
            ->extends('adminlte::page')
            ->section('content');
    }
}
