<?php

namespace App\Http\Livewire\InternetCustomer\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Jobs\ProvisionCustomerJob;
use App\Jobs\GenerateInternetPurchaseCouponJob;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\InternetCustomer;
use App\Models\InternetCustomerPurchase;

use App\Jobs\GenerateBillingJob;
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

        // Properties untuk edit data pribadi
    public $name, $email, $phone_number, $start_billing_date, $end_billing_date;
    
    // Properties untuk edit data instalasi
    public $P, $username, $pass_hash, $device_serial_number;

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

    // Buka modal edit data pribadi
    public function openEditPribadiModal()
    {
        $this->name = $this->customer->name;
        $this->email = $this->customer->userCustomer->email ?? '';
        $this->phone_number = $this->customer->userCustomer->phone_number ?? '';
        $this->start_billing_date = $this->customer->userCustomer->start_billing_date ?? '';
        $this->end_billing_date = $this->customer->userCustomer->end_billing_date ?? '';
        
        $this->dispatchBrowserEvent('showEditPribadiModal', [
            'name' => $this->customer->name,
            'email' => $this->customer->userCustomer->email ?? '',
            'phone_number' => $this->customer->userCustomer->phone_number ?? '',
            'start_billing_date' => $this->customer->userCustomer->start_billing_date ?? '',
            'end_billing_date' => $this->customer->userCustomer->end_billing_date ?? '',
        ]);
    }

    // Simpan data pribadi
    public function savePribadi()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string',
            'start_billing_date' => 'nullable|date',
            'end_billing_date' => 'nullable|date|after:start_billing_date',
        ], [
            'name.required' => 'Nama harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'phone_number.string' => 'Nomor telepon harus berupa angka.',
            'start_billing_date.date' => 'Format tanggal mulai tagihan tidak valid.',
            'end_billing_date.date' => 'Format tanggal akhir tagihan tidak valid.',
            'end_billing_date.after' => 'Tanggal akhir tagihan harus setelah tanggal mulai tagihan.',
        ]);

        DB::beginTransaction();
        try {
            // Update data customer
            $this->customer->update([
                'name' => $this->name,
            ]);

            // Update data user customer
            if ($this->customer->userCustomer) 
            {   
                if($this->customer->userCustomer->start_billing_date != $this->start_billing_date && $this->start_billing_date == Carbon::now()->format('Y-m-d')
                    // && !in_array($customer->internetCustomer->status, [
                    //     ParamSchema::ACTIVE,
                    //     ParamSchema::INSTALLED]
                    //     )
                    )
                {
                    GenerateBillingJob::dispatch($this->customer->userCustomer);
                }
                $this->customer->userCustomer->update([
                    'name' => $this->name,
                    'email' => $this->email ? $this->email : null,
                    'phone_number' => $this->phone_number,
                    'start_billing_date' => $this->start_billing_date,
                    'end_billing_date' => $this->end_billing_date,
                ]);
            }

            DB::commit();
            $this->dispatchBrowserEvent('hideEditPribadiModal');
            $this->dispatchBrowserEvent('showSuccessAlert', ['message' => 'Data pribadi berhasil diperbarui']);
            
            // Refresh data
            $this->mount($this->customer->id);
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            $this->dispatchBrowserEvent('showErrorAlert', ['message' => 'Gagal memperbarui data pribadi: ' . $e->getMessage()]);
        }
    }

    // Buka modal edit data instalasi
    public function openEditInstalasiModal()
    {
        $this->local_address = $this->customer->local_address ?? '';
        $this->username = $this->customer->username ?? '';
        $this->pass_hash = $this->customer->pass_hash ?? '';
        $this->device_serial_number = $this->customer->installation->device_serial_number ?? '';
        
        $this->dispatchBrowserEvent('showEditInstalasiModal',
        [
            'local_address' => $this->customer->local_address ?? '',
            'username' => $this->customer->username ?? '',
            'pass_hash' => $this->customer->pass_hash ?? '',
            'device_serial_number' => $this->customer->installation->device_serial_number ?? '',
        ]);
    }

    // Simpan data instalasi
    public function saveInstalasi()
    {
        $this->validate([
            'local_address' => 'nullable|ip',
            'username' => 'nullable|string|max:255',
            'pass_hash' => 'nullable|string|max:255',
            'device_serial_number' => 'nullable|string|max:255',
        ],[
            'local_address.ip' => 'Format IP tidak valid.',
            'username.string' => 'Username harus berupa teks.',
            'pass_hash.string' => 'Password harus berupa teks.',
            'device_serial_number.string' => 'Nomor seri perangkat harus berupa teks.',
        ]);

        DB::beginTransaction();
        try {
            // Update data customer
            $this->customer->update([
                'status' => ParamSchema::REACTIVATED,
                'local_address' => $this->local_address,
                'username' => $this->username,
                'pass_hash' => $this->pass_hash,
            ]);

            // Update data instalasi
            if ($this->customer->installation) {
                $this->customer->installation->update([
                    'device_serial_number' => $this->device_serial_number,
                ]);
            }

        dispatch(new ProvisionCustomerJob($this->customer->id));
            
            DB::commit();
            $this->dispatchBrowserEvent('hideEditInstalasiModal');
            $this->dispatchBrowserEvent('showSuccessAlert', ['message' => 'Data instalasi berhasil diperbarui']);
            
            // Refresh data
            $this->mount($this->customer->id);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchBrowserEvent('showErrorAlert', ['message' => 'Gagal memperbarui data instalasi: ' . $e->getMessage()]);
        }
    }

  public function viewPaymentProof($purchaseId)
  {
        $purchase = InternetCustomerPurchase::find($purchaseId);
        $this->paymentProofUrl = $purchase->payment_proof ? s3_asset(true,10,$purchase->payment_proof) : null;

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

             $startDate = Carbon::parse($internetCustomers->start_billing_date);
            if ($startDate->isSameMonth(now())) {
                $date = $startDate; // tetap Carbon object
            } else {
                $date = now();
            }
            
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
            }else
            {
                $post['status'] = ParamSchema::REACTIVATED;
            }
            
            GenerateInternetPurchaseCouponJob::dispatch($internetPurchase->customer->id, $internetPurchase->id, $internetPurchase->payment_months);

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