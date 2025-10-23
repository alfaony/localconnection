<?php

namespace App\Http\Livewire\InternetCustomer;

use Livewire\Component;
use App\Models\InternetCustomer;
use App\Models\Company;
use App\Models\InternetCustomerPurchase;

use Carbon\Carbon;

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
            'purchases',
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

        // Get KTP photo URL
        $this->ktpPhotoUrl = $this->customer->ktp_photo;

        // Get installation photos if exists
        if ($this->customer->installation && $this->customer->installation->photos) {
            $this->installationPhotos = json_decode($this->customer->installation->photos, true);
        }
    }

    public function viewPaymentProof($purchaseId)
    {
        $purchase = InternetCustomerPurchase::find($purchaseId);
        $this->paymentProofUrl = $purchase->payment_proof;
        $this->dispatchBrowserEvent('showImageModal', [
            'title' => 'Bukti Pembayaran ' . Carbon::parse($purchase->period)->format('F Y'),
            'imageUrl' => $this->paymentProofUrl
        ]);
    }
    public function viewKtpPhoto()
    {
        $this->dispatchBrowserEvent('showImageModal', [
            'title' => 'Foto KTP',
            'imageUrl' => s3_asset(true,10, $this->ktpPhotoUrl)
        ]);
    }

    public function viewInstallationPhotos()
    {
        // Convert paths to full URLs
        $fullUrls = array_map(function($path) {
            return s3_asset(true,10, $path);
        }, $this->installationPhotos);
        
        $this->dispatchBrowserEvent('showGalleryModal', [
            'title' => 'Foto Instalasi',
            'images' => $fullUrls
        ]);
    }

    public function render()
    {
        $purchases = $this->customer->purchases()->orderby('created_at')->paginate(5);
        return view('livewire.internet-customer.internet-customer-show', compact('purchases'))
            ->extends('layouts.app_customer');
    }
}
