<?php

namespace App\Http\Livewire\InternetCustomer;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Models\InternetCustomer;
use App\Models\InternetCustomerPurchase;
use App\Models\SettingCompany;
use Carbon\Carbon;
use App\Helpers\InboxHelper;
use App\Models\User;
use App\Schemas\RoleSchema;
use App\Schemas\ParamSchema;
use App\Jobs\ProvisionCustomerJob;


class InternetCustomerShow extends Component
{
    use WithFileUploads;

    public $customer;
    public $agreementFields;
    public $paymentProofUrl;
    public $ktpPhotoUrl;
    public $installationPhotos = [];
    public $code;

    public $payment_proof;
    public $purchase_id;
    public $modalData = [];

    protected $rules = [
        'payment_proof' => 'required|image|max:2048',
    ];

    protected $messages = [
        'payment_proof.required' => 'Bukti pembayaran wajib diupload.',
        'payment_proof.image' => 'File harus berupa gambar.',
        'payment_proof.max' => 'Ukuran file tidak boleh lebih dari 2MB.',
    ];

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

        if ($this->customer->partnershipAgreement) {
            $this->agreementFields = json_decode($this->customer->partnershipAgreement->fields, true);
        }

        $this->ktpPhotoUrl = $this->customer->ktp_photo;

        if ($this->customer->installation && $this->customer->installation->photos) {
            $this->installationPhotos = json_decode($this->customer->installation->photos, true);
        }
    }

    public function showPaymentModal($purchaseId)
    {
        $purchase = InternetCustomerPurchase::find($purchaseId);
        $companySettings = SettingCompany::byCompany($purchase->customer->company_id)
                            ->whereIn('menu', ['bank', 'profile'])
                            ->get()
                            ->pluck('field_value', 'field_title');
        
        $this->purchase_id = $purchase->id;

        $modalData = [
            'packageName' => $purchase->customer->internetPackage->name,
            'amount' => $purchase->customer->internetPackage->price_nett,
            'method' => "Transfer Bank",
            'bank' => $companySettings['nama_bank'] ?? 'Bank Tidak Diketahui',
            'account' => $companySettings['rekening_number'] ?? 'Nomor Rekening Tidak Diketahui',
            'accountName' => $companySettings['atas_nama'] ?? 'Nama Pemilik Tidak Diketahui'
        ];

        $this->dispatchBrowserEvent('show-payment-modal', $modalData);
    }

    public function submitPaymentProof()
    {
        // dd($this->payment_proof, "payment_proof");
        // $this->validate();

        try {
            $purchase = InternetCustomerPurchase::findOrFail($this->purchase_id);
            $internetCustomer = $purchase->customer;

            if($internetCustomer->status == ParamSchema::SUSPENDED)
            {
                dispatch(new ProvisionCustomerJob($cust->id));
            }
            
            $internetCustomer->update([
                'status' => ParamSchema::WAITING_PAYMENT_CONFIRMATION
            ]);
            
            // Store the file
            $path = $this->payment_proof->store('payment_proofs', 'public');
            
            // Update purchase record
            $purchase->update([
                'payment_proof' => $path,
                'payment_method' => 'transfer',
                'payment_date' => now(),
            ]);


            $userFinance = User::whereHas('role.permissions', function ($q) 
                {
                    $q->where('method', 'as_finance')
                    ->where('table', 'internet_customers');
                })
                ->where(function ($q) use ($internetCustomer) {
                    $q->where('company_id', $internetCustomer->company_id)
                    ->orWhereHas('accessibleCompanies', function ($sub) use ($internetCustomer) {
                        $sub->where('companies.id', $internetCustomer->company_id);
                    });
                })->get();

                if($userFinance)
                {
                    $from = User::where('company_id', $internetCustomer->company_id)
                    ->where(function ($query) {
                        $query->whereHas('role', function ($q)  {
                            $q->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN]);
                        });
                    })
                    ->first();
                    
                    $message = "Pelanggan dengan kode ".$internetCustomer->code." telah berhasil mendaftar. Silakan periksa detail pendaftaran dan tindak lanjuti.";
                    $directUrl = route('internet-customer.index');
                    foreach($userFinance as $finance)
                    {
                        $this->sentInbox($finance->id, $from->id, $message, $directUrl);
                    }   
                }
            // Reset file input
            $this->reset('payment_proof');
            
            // Close modal
            $this->dispatchBrowserEvent('hide-payment-modal');
            
            // Show success message
            return redirect()->back()->with('success', 'Bukti pembayaran berhasil dikirim dan sedang menunggu konfirmasi.');            
        } catch (\Exception $e) {
            // dd($e);
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function viewPaymentProof($purchaseId)
    {
        $purchase = InternetCustomerPurchase::find($purchaseId);
        $this->paymentProofUrl = $purchase->payment_proof ? Storage::url($purchase->payment_proof) : null;
        
        if ($this->paymentProofUrl) {
            $this->dispatchBrowserEvent('showImageModal', [
                'title' => 'Bukti Pembayaran ' . Carbon::parse($purchase->period)->format('F Y'),
                'imageUrl' => $this->paymentProofUrl
            ]);
        }
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
        $purchases = $this->customer->purchases()->orderBy('created_at', 'desc')->paginate(5);
        return view('livewire.internet-customer.internet-customer-show', compact('purchases'))
            ->extends('layouts.app_customer');
    }

    private function sentInbox($to,$from, $message,$directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $to, 
            $from,
            $message, 
            $directUrl
        );
        return true;
    }
}