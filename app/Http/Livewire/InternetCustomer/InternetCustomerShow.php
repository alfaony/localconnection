<?php

namespace App\Http\Livewire\InternetCustomer;

use Livewire\Component;
use App\Models\InternetCustomer;
use App\Models\Company;

class InternetCustomerShow extends Component
{
    public $customer;
    public $agreementFields;
    public $paymentProofUrl;
    public $showPaymentProofModal = false;
    public $ktpPhotoUrl;
    public $showKtpModal = false;
    public $installationPhotos = [];
    public $showInstallationPhotosModal = false;
    public $code;

    public function mount($code)
    {
        $this->customer = InternetCustomer::with([
            'company',
            'province',
            'city',
            'district',
            'subdistrict',
            'internetPackage',
            'partnershipAgreement',
            'userCustomer',
            'purchase',
            'installation'
        ])->where('code', $code)->first();

        if(!$this->customer) 
        {
            return redirect()->route('public.error', ['code' => 403])->with([
                'title' => 'Akses Ditolak',
                'message' => 'Terdapat Kesalahan pada Link Akun',
                'icon' => 'fas fa-ban'
            ]);
        }

        // Decode agreement fields if exists
        if ($this->customer->partnershipAgreement) {
            $this->agreementFields = json_decode($this->customer->partnershipAgreement->fields, true);
        }

        // Get payment proof URL if exists
        if ($this->customer->purchase && $this->customer->purchase->payment_proof) {
            $this->paymentProofUrl = $this->customer->purchase->payment_proof;
        }

        // Get KTP photo URL
        $this->ktpPhotoUrl = $this->customer->ktp_photo;

        // Get installation photos if exists
        if ($this->customer->installation && $this->customer->installation->photos) {
            $this->installationPhotos = json_decode($this->customer->installation->photos, true);
        }
    }

    public function viewPaymentProof()
    {
        $this->dispatchBrowserEvent('showImageModal', [
            'title' => 'Bukti Pembayaran',
            'imageUrl' => $this->paymentProofUrl
        ]);
    }

    public function viewKtpPhoto()
    {
        $this->dispatchBrowserEvent('showImageModal', [
            'title' => 'Foto KTP',
            'imageUrl' => $this->ktpPhotoUrl
        ]);
    }

    public function viewInstallationPhotos()
    {
        // Convert paths to full URLs
        $fullUrls = array_map(function($path) {
            return asset('storage/' . $path);
        }, $this->installationPhotos);
        
        $this->dispatchBrowserEvent('showGalleryModal', [
            'title' => 'Foto Instalasi',
            'images' => $fullUrls
        ]);
    }

    public function render()
    {
        return view('livewire.internet-customer.internet-customer-show')
            ->extends('layouts.app_customer');
    }
}
