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