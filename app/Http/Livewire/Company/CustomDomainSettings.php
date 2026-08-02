<?php

namespace App\Http\Livewire\Company;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CustomDomainSettings extends Component
{
    public string $domainInput = '';
    public ?array $verificationInstructions = null;
    public bool $verifying = false;
    public ?string $verifyResult = null;

    public function mount()
    {
        $company = $this->company();
        if ($company->custom_domain) {
            $this->domainInput = $company->custom_domain;
            if (!$company->custom_domain_verified_at) {
                $this->verificationInstructions = [
                    'domain' => $company->custom_domain,
                    'txt_record_name' => "_keloola-verify.{$company->custom_domain}",
                    'txt_record_value' => $company->custom_domain_verification_token,
                    'cname_target' => config('app.tenant_cname_target', 'tenants.internetrt.com'),
                ];
            }
        }
    }

    protected function company(): Company
    {
        return Company::findOrFail(Auth::user()->company_id);
    }

    public function submitDomain()
    {
        $this->validate([
            'domainInput' => ['required', 'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/i'],
        ], [
            'domainInput.regex' => 'Format domain tidak valid. Contoh yang benar: www.domainsaya.com',
        ]);

        $company = $this->company();
        $this->verificationInstructions = $company->requestCustomDomain($this->domainInput);
        $this->verifyResult = null;

        session()->flash('message', 'Domain disimpan. Ikuti instruksi DNS di bawah, lalu klik "Verifikasi" setelah DNS ke-propagate (bisa sampai 24 jam).');
    }

    public function verify()
    {
        $this->verifying = true;
        $company = $this->company();

        $success = $company->verifyCustomDomain();

        $this->verifying = false;
        $this->verifyResult = $success
            ? 'success'
            : 'failed';

        if ($success) {
            session()->flash('message', "Domain {$company->custom_domain} berhasil diverifikasi dan aktif!");
        }
    }

    public function removeCustomDomain()
    {
        $company = $this->company();
        $company->update([
            'custom_domain' => null,
            'custom_domain_verification_token' => null,
            'custom_domain_verified_at' => null,
        ]);

        $this->domainInput = '';
        $this->verificationInstructions = null;
        $this->verifyResult = null;

        session()->flash('message', 'Custom domain dihapus. URL kembali ke subdomain gratis.');
    }

    public function render()
    {
        return view('livewire.company.custom-domain-settings', [
            'company' => $this->company(),
        ])->extends('adminlte::page');
    }
}
