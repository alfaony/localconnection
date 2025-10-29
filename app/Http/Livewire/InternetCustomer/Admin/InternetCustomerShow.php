<?php

namespace App\Http\Livewire\InternetCustomer\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\InternetCustomer;
use App\Models\InternetCustomerPurchase;

use App\Schemas\ParamSchema;
use App\Helpers\InboxHelper;
use Carbon\Carbon;

class InternetCustomerShow extends Component
{
    use WithPagination;

    public $customer;
    public $agreementFields;
    public $paymentProofUrl;
    public $showPaymentProofModal = false;
    public $ktpPhotoUrl;
    public $showKtpModal = false;
    public $installationPhotos = [];
    public $showInstallationPhotosModal = false;

    public function mount($customerId)
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
        ])->findOrFail($customerId);

        // Decode agreement fields if exists
        if ($this->customer->partnershipAgreement) {
            $this->agreementFields = json_decode($this->customer->partnershipAgreement->fields, true);
        }


        // Get KTP photo URL
        $this->ktpPhotoUrl = $this->customer->ktp_photo;

        // Get installation photos if exists
        if ($this->customer->installation && $this->customer->installation->medias->count() > 0) {
            $this->installationPhotos = $this->customer->installation->medias
                ->pluck('photo')
                ->toArray();
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

    public function confirmPayment($customerId)
    {
        $internetPurchase = InternetCustomerPurchase::findOrFail($customerId);
        $internetCustomers = $internetPurchase->customer->userCustomer;

        DB::beginTransaction();
        try {
            $internetPurchase->update([
                'confirmation_finance_at' => now(),
                'user_finance_id' => Auth::user()->id
            ]);

            $internetCustomers->update([
                'start_billing_date' => $date->addMonth()->firstOfMonth()->format('Y-m-d'),
                'end_billing_date' => $date->addDays(config('services.internet_custom.end_billing_of_days'))->format('Y-m-d')
            ]);
    
            $post =[
                'is_paid' => true,
            ];
    
        
    
            if(!$internetPurchase->customer->installation)
            {
                $post['status'] = ParamSchema::PROCESS_INSTALLATION;
                
                $userTechnical = optional($internetPurchase->customer->subdistrict?->coverageService?->coverageServiceOds)
                ->pluck('ods.user_assign_id')
                ->unique()
                ->all();
        
                if(count($userTechnical) > 0)
                {
                    $message = "Pembayaran Langganan Internet Untuk Kode ".$internetPurchase->customer->code." Telah di Setujui Oleh Finance Silahkan segera lakukan Pemasangan";
                    $directUrl = route('internet-customer.show',$internetPurchase->customer->id);
                    foreach($userTechnical as $tech)
                    {
                        $this->sentInbox($tech,$message, $directUrl);
                    }
                }
            }
    
            $internetPurchase->customer->update($post);
            DB::commit();
    
            $this->dispatchBrowserEvent('showSuccessAlert', ['message' => 'Pembayaran Langganan Internet Untuk Kode '.$internetPurchase->customer->code.' Telah di Setujui Oleh Finance Silahkan segera lakukan Pemasangan']);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error($th);
            DB::rollBack();
            $this->dispatchBrowserEvent('showErrorAlert', ['message' => 'Gagal mengkonfirmasi pembayaran: ' . $th->getMessage()]);
        }
    }

    
    public function viewKtpPhoto()
    {
        $url = s3_asset(true,10,$this->ktpPhotoUrl);
        $this->dispatchBrowserEvent('showImageModal', [
            'title' => 'Foto KTP',
            'imageUrl' => $url
        ]);
    }

    public function viewInstallationPhotos()
    {
        // Cek apakah ada foto instalasi
        if (empty($this->installationPhotos)) {
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Foto instalasi tidak tersedia'
            ]);
            return;
        }

        // Convert paths to full URLs menggunakan helper s3_asset
        // Parameter: s3_asset($private = true, $expiresInMinutes = 10, $path)
        $fullUrls = array_map(function($path) {
            return s3_asset(true, 10, $path);
        }, $this->installationPhotos);

        // Filter out empty or invalid URLs
        $fullUrls = array_filter($fullUrls, function($url) {
            return !empty($url);
        });

        // Cek apakah ada URL yang valid
        if (empty($fullUrls)) {
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Tidak dapat memuat foto instalasi'
            ]);
            return;
        }

        // Dispatch event ke JavaScript untuk menampilkan gallery
        $this->dispatchBrowserEvent('showGalleryModal', [
            'title' => 'Foto Instalasi - ' . $this->customer->code,
            'images' => array_values($fullUrls) // Re-index array untuk JavaScript
        ]);
    }

    public function render()
    {
        $purchases = $this->customer->purchases()->orderby('created_at')->paginate(5);
        return view('livewire.internet-customer.admin.internet-customer-show', compact('purchases'))
            ->extends('adminlte::page');
    }

    private function sentInbox($to,$message,$directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $to, 
            Auth::user()->id, 
            $message, 
            $directUrl
        );
        return true;
    }
}