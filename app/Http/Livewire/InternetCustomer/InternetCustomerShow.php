<?php

namespace App\Http\Livewire\InternetCustomer;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\InternetCustomer;
use App\Models\InternetCustomerPurchase;
use App\Models\SettingCompany;
use Carbon\Carbon;
use App\Helpers\InboxHelper;
use App\Models\User;
use App\Schemas\RoleSchema;
use App\Schemas\ParamSchema;
use App\Jobs\ProvisionCustomerJob;
use App\Services\XenditService;
use App\Services\MidtransService;

class InternetCustomerShow extends Component
{
    use WithFileUploads;

    public $customer;
    public $paymentProofUrl;
    public $ktpPhotoUrl;
    public $installationPhotos = [];
    public $code;
    public $statusMessage;

    public $payment_proof;
    public $purchase_id;
    public $modalData = [];
    public $payment_method_choice = 'manual';
    public $payment_months = 1;
    
    // Transfer detail fields
    public $transfer_date;
    public $transfer_from_bank;
    public $transfer_from_account_name;
    public $transfer_notes;
    public $xenditActive = false;
    public $midtransActive = false;
    public $qrisGopayActive = false;
    public $xenditPayWithPpn = false;
    public $midtransPayWithPpn = false;
    public $manualPaymentEnabled = false; // Default disabled, loaded from settings

    // Calculated values
    public $monthlyPrice = 0;
    public $subtotal = 0;
    public $discountPercentage = 0;
    public $discountAmount = 0;
    public $amountBeforeTax = 0;
    public $taxRate = 11; // Default PPN 11%
    public $taxAmount = 0;
    public $totalAmount = 0;

    protected $rules = [
        'payment_proof' => 'required|image|max:2048',
        'payment_months' => 'required|integer|min:1|max:12',
        'transfer_date' => 'required|date|before_or_equal:today',
        'transfer_from_bank' => 'nullable|string|max:255',
        'transfer_from_account_name' => 'nullable|string|max:255',
        'transfer_notes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'payment_proof.required' => 'Bukti pembayaran wajib diupload.',
        'payment_proof.image' => 'File harus berupa gambar.',
        'payment_proof.max' => 'Ukuran file tidak boleh lebih dari 2MB.',
        'payment_months.required' => 'Jumlah bulan pembayaran harus dipilih.',
        'payment_months.min' => 'Minimal pembayaran adalah 1 bulan.',
        'payment_months.max' => 'Maksimal pembayaran adalah 12 bulan.',
        'transfer_date.required' => 'Tanggal transfer wajib diisi.',
        'transfer_date.date' => 'Format tanggal tidak valid.',
        'transfer_date.before_or_equal' => 'Tanggal transfer tidak boleh lebih dari hari ini.',
        'transfer_from_bank.max' => 'Nama bank maksimal 255 karakter.',
        'transfer_from_account_name.max' => 'Nama pemilik rekening maksimal 255 karakter.',
        'transfer_notes.max' => 'Catatan maksimal 500 karakter.',
    ];

    public function mount($code)
    {
        $this->customer = InternetCustomer::with([
            'company',
            'province',
            'city',
            'district',
            'subdistrict',
            'internetPackage.regions',  // eager-load regions untuk harga per wilayah
            'userCustomer',
            'purchases',
            'installation'
        ])->where('code', $code)->first();
        
        if(!$this->customer) 
        {
            return redirect()->route('public.error', ['code' => 500])
                ->with('message', 'Kode pelanggan tidak ditemukan. Silakan periksa kembali atau hubungi admin.');
        }

        $status = request()->query('status'); // ambil dari query string
        $purchase = request()->query('purchase'); // ambil dari query string

        $progressStatus = $this->customer->status;

        if ($status === 'success' 
        // && $progressStatus == ParamSchema::WAITING_PAYMENT_SUBSCRIPTION
        ) {
            // $internetCustomerPurchase = InternetCustomerPurchase::where('id', $purchase)->first();
            // if($internetCustomerPurchase) 
            // {
            //     $this->afterPayment($internetCustomerPurchase);
            // }
            $this->statusMessage = [
                'type' => 'success',
                'text' => '🎉 Pembayaran berhasil! Terima kasih sudah menggunakan layanan kami.'
            ];
        } elseif ($status === 'failed' 
        // && $progressStatus == ParamSchema::WAITING_PAYMENT_SUBSCRIPTION
        ) 
        {
            $this->statusMessage = [
                'type' => 'danger',
                'text' => '⚠️ Transaksi gagal diproses. Silakan coba lagi atau hubungi admin.'
            ];
        }

        if(!$this->customer) 
        {
            return redirect()->route('public.error', ['code' => 403])->with([
                'title' => 'Akses Ditolak',
                'message' => 'Terdapat Kesalahan pada Link Akun',
                'icon' => 'fas fa-ban'
            ]);
        }

        $this->ktpPhotoUrl = $this->customer->ktp_photo;

        if ($this->customer->installation && $this->customer->installation->photos) {
            $this->installationPhotos = json_decode($this->customer->installation->photos, true);
        }

        $this->checkXenditStatus();
        $this->checkMidtransStatus();
        $this->loadPpnSettings();
        $this->calculatePayment();
    }

    protected function loadPpnSettings()
    {
        // Load payment gateway settings
        $companySettings = SettingCompany::byCompany($this->customer->company_id)->get()->pluck('field_value', 'field_title');
        $midtransService = new MidtransService($this->customer->company_id);
        
        $this->midtransActive = $midtransService->testConnectionCheck()['success'] ? true : false;

        $this->qrisGopayActive = $this->midtransActive;
        $this->xenditActive = isset($companySettings['secret_key']) && isset($companySettings['webhook_token']) ? true : false;

        $this->xenditPayWithPpn = isset($companySettings['xendit_pay_with_ppn']) && $companySettings['xendit_pay_with_ppn'] == '1';
        $this->midtransPayWithPpn = isset($companySettings['midtrans_pay_with_ppn']) && $companySettings['midtrans_pay_with_ppn'] == '1';
        $this->manualPaymentEnabled = isset($companySettings['manual_payment_status']) && $companySettings['manual_payment_status'] == '1';
    }

    protected function checkXenditStatus()
    {
        try {
            $xenditService = new XenditService($this->customer->company_id);
            $this->xenditActive = $xenditService->isActive();
        } catch (\Exception $e) {
            $this->xenditActive = false;
            Log::warning('Xendit not configured', [
                'company_id' => $this->customer->company_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function checkMidtransStatus()
    {
        try {
            Log::info('InternetCustomerShow - Checking Midtrans Status', [
                'customer_id' => $this->customer->id,
                'customer_company_id' => $this->customer->company_id,
                'auth_company_id' => auth()->user()->company_id ?? 'not set'
            ]);
            
            $midtransService = new MidtransService($this->customer->company_id);
            $this->midtransActive = $midtransService->testConnection();
            $this->qrisGopayActive = $this->midtransActive;

            Log::info('InternetCustomerShow - Midtrans Status Result', [
                'company_id' => $this->customer->company_id,
                'midtransActive' => $this->midtransActive,
                'testConnection_result' => $this->midtransActive ? 'SUCCESS' : 'FAILED'
            ]);

        } catch (\Exception $e) {
            $this->midtransActive = false;
            $this->qrisGopayActive = false;
            Log::warning('Midtrans not configured', [
                'company_id' => $this->customer->company_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function updatedPaymentMonths($value)
    {
        // Validasi input
        $this->payment_months = max(
            InternetCustomerPurchase::MIN_MONTHS, 
            min(InternetCustomerPurchase::MAX_MONTHS, (int)$value)
        );
        
        $this->calculatePayment();
        $this->dispatchBrowserEvent('payment-calculated', [
            'months' => $this->payment_months,
            'calculation' => [
                'monthly_price' => $this->monthlyPrice,
                'subtotal' => $this->subtotal,
                'discount_percentage' => $this->discountPercentage,
                'discount_amount' => $this->discountAmount,
                'total' => $this->totalAmount
            ]
        ]);
    }

    protected function calculatePayment()
    {
        $package = $this->customer->internetPackage;
        if (!$package) {
            $this->monthlyPrice = 0;
            return;
        }

        // Ambil harga sesuai wilayah customer: district > city > province > global
        $priceData = $package->getPriceForRegion(
            $this->customer->province_id,
            $this->customer->city_id,
            $this->customer->district_id
        );
        $this->monthlyPrice = $priceData['price'];

        $calculation = InternetCustomerPurchase::calculateTotal(
            $this->monthlyPrice,
            $this->payment_months
        );

        $this->subtotal           = $calculation['subtotal'];
        $this->discountPercentage = $calculation['discount_percentage'];
        $this->discountAmount     = $calculation['discount_amount'];
        $this->amountBeforeTax    = round($calculation['total']);

        // ALWAYS calculate and display PPN in UI
        $this->taxAmount   = round(($this->amountBeforeTax * $this->taxRate) / 100);
        $this->totalAmount = round($this->amountBeforeTax + $this->taxAmount);
    }

    

    public function showPaymentModal($purchaseId)
    {
        $purchase = InternetCustomerPurchase::find($purchaseId);
        $companySettings = SettingCompany::byCompany($purchase->customer->company_id)
                            ->whereIn('menu', ['bank', 'profile'])
                            ->get()
                            ->pluck('field_value', 'field_title');
        
        $this->purchase_id = $purchase->id;
        $this->payment_months = 1;
        $this->calculatePayment();

        // Hitung periode yang akan dibayar untuk preview
        $previewPeriod = $this->calculateSubscriptionPeriod(1);

        $modalData = [
            'purchaseId' => $purchase->id,
            'packageName' => $purchase->customer->internetPackage->name,
            'monthlyPrice' => $this->monthlyPrice,
            'bank' => $companySettings['nama_bank'] ?? 'Bank Tidak Diketahui',
            'account' => $companySettings['rekening_number'] ?? 'Nomor Rekening Tidak Diketahui',
            'accountName' => $companySettings['atas_nama'] ?? 'Nama Pemilik Tidak Diketahui',
            'xenditActive' => $this->xenditActive,
            'midtransActive' => $this->midtransActive,
            'qrisGopayActive' => $this->qrisGopayActive,
            'nextPeriodStart' => $previewPeriod['start']->format('d M Y'),
            'currentBillingEnd' => $this->customer->userCustomer->end_billing_date 
                ? Carbon::parse($this->customer->userCustomer->end_billing_date)->format('d M Y')
                : 'Belum ada periode aktif',
            'discountEnabled' => InternetCustomerPurchase::ENABLE_DISCOUNT,
            'discountTiers' => InternetCustomerPurchase::getDiscountTiers(),
            'minMonths' => InternetCustomerPurchase::MIN_MONTHS,
            'maxMonths' => InternetCustomerPurchase::MAX_MONTHS
        ];

        $this->dispatchBrowserEvent('show-payment-modal', $modalData);
    }

    protected function calculateSubscriptionPeriod($months)
    {
        $customer = $this->customer;
        
        // Ambil tanggal billing terakhir atau gunakan hari ini
        $lastBillingDate = $customer->userCustomer->end_billing_date 
            ? Carbon::parse($customer->userCustomer->end_billing_date)->addDay() // Mulai sehari setelah periode terakhir berakhir
            : now()->startOfDay();

        // Hitung periode berakhir
        $periodStart = $lastBillingDate->copy();
        $periodEnd = $lastBillingDate->copy()->addMonths($months)->subDay();

        return [
            'start' => $periodStart,
            'end' => $periodEnd
        ];
    }


    public function payWithXendit()
    {
        try {
            if (!$this->purchase_id) {
                session()->flash('error', 'Data pembayaran tidak ditemukan.');
                return redirect()->back();
            }

            $purchase = InternetCustomerPurchase::findOrFail($this->purchase_id);
            $internetCustomer = $purchase->customer;

            $xenditService = new XenditService($internetCustomer->company_id);

            if (!$xenditService->isActive()) {
                session()->flash('error', 'Pembayaran Xendit tidak tersedia untuk saat ini.');
                $this->dispatchBrowserEvent('hide-payment-modal');
                return redirect()->back();
            }

            // Calculate period
            $periodStart = $internetCustomer->userCustomer->start_billing_date 
                ? Carbon::parse($internetCustomer->userCustomer->start_billing_date)
                : now();
            
            $periodEnd = $periodStart->copy()->addMonths($this->payment_months)->subDay();

            // Update purchase with period info
            $purchase->update([
                'payment_months' => $this->payment_months,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'total_before_discount' => $this->subtotal,
                'amount_before_tax' => $this->amountBeforeTax,
                'tax_rate' => $this->taxRate,
                'tax_amount' => $this->taxAmount,
                'discount_amount' => $this->discountAmount,
                'amount_paid' => $this->totalAmount,
            ]);

            Log::info('Creating Xendit invoice', [
                'purchase_id' => $purchase->id,
                'customer_id' => $internetCustomer->id,
                'company_id' => $internetCustomer->company_id,
                'payment_months' => $this->payment_months,
                'total_amount' => $this->totalAmount
            ]);

            $result = $xenditService->createInvoiceKeloolaPay($purchase, $internetCustomer, [
                'payment_months' => $this->payment_months,
                'total_amount' => $this->xenditPayWithPpn ? $this->amountBeforeTax : $this->totalAmount,
                'discount_amount' => $this->discountAmount,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'xendit_pay_with_ppn' => $this->xenditPayWithPpn
            ]);

            if ($result['success']) 
            {
                $invoice = $result['data'];

                $purchase->update([
                    'xendit_invoice_id' => $invoice['id'],
                    // 'xendit_invoice_url' => $invoice['invoice_url'],
                    'payment_method' => 'xendit',
                    'xendit_raw_response' => json_encode($invoice),
                ]);

                Log::info('Xendit invoice created successfully', [
                    'invoice_id' => $invoice['id'],
                    'invoice_url' => $invoice['invoice_url'] ?? null
                ]);

                
                $this->dispatchBrowserEvent('hide-payment-modal');
                return redirect()->away($invoice['url_payment'].$result['token']);
                
            } else {
                Log::error('Failed to create Xendit invoice', [
                    'message' => $result['message']
                ]);
                
                session()->flash('error', 'Gagal membuat invoice pembayaran: ' . $result['message']);
                $this->dispatchBrowserEvent('hide-payment-modal');
                return redirect()->back();
            }

        } catch (\Exception $e) {
            // dd($e);
            Log::error('Error in payWithXendit', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
            $this->dispatchBrowserEvent('hide-payment-modal');
            return redirect()->back();
        }
    }

    public function payWithMidtrans()
    {
        try {
            if (!$this->purchase_id) {
                session()->flash('error', 'Data pembayaran tidak ditemukan.');
                return redirect()->back();
            }

            $purchase = InternetCustomerPurchase::findOrFail($this->purchase_id);
            $internetCustomer = $purchase->customer;

            $midtransService = new MidtransService($internetCustomer->company_id);

            if (!$midtransService->isActive()) {
                session()->flash('error', 'Pembayaran Midtrans tidak tersedia untuk saat ini.');
                $this->dispatchBrowserEvent('hide-payment-modal');
                return redirect()->back();
            }

            // Calculate period
            $periodStart = $internetCustomer->userCustomer->start_billing_date 
                ? Carbon::parse($internetCustomer->userCustomer->start_billing_date)
                : now();
            
            $periodEnd = $periodStart->copy()->addMonths($this->payment_months)->subDay();

            // Update purchase with period info
            $purchase->update([
                'payment_months' => $this->payment_months,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'total_before_discount' => $this->subtotal,
                'discount_amount' => $this->discountAmount,
                'amount_before_tax' => $this->amountBeforeTax,
                'tax_rate' => $this->taxRate,
                'tax_amount' => $this->taxAmount,
                'amount_paid' => $this->totalAmount,
            ]);

            Log::info('Creating Midtrans transaction', [
                'purchase_id' => $purchase->id,
                'customer_id' => $internetCustomer->id,
                'company_id' => $internetCustomer->company_id,
                'payment_months' => $this->payment_months,
                'total_amount' => $this->totalAmount
            ]);

            $result = $midtransService->createTransaction($purchase, $internetCustomer, [
                'payment_months' => $this->payment_months,
                'total_amount' => $this->totalAmount,
                'discount_amount' => $this->discountAmount,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'midtrans_pay_with_ppn' => $this->midtransPayWithPpn
            ]);

            if ($result['success']) 
            {
                $purchase->update([
                    'midtrans_snap_token' => $result['snap_token'],
                    'midtrans_transaction_id' => $result['order_id'],
                    'payment_method' => 'midtrans',
                    'midtrans_raw_response' => $result['raw_response'],
                ]);

                Log::info('Midtrans transaction created successfully', [
                    'order_id' => $result['order_id'],
                    'snap_token' => $result['snap_token']
                ]);

                $this->dispatchBrowserEvent('hide-payment-modal');
                return redirect()->away($result['redirect_url']);
                
            } else {
                Log::error('Failed to create Midtrans transaction', [
                    'message' => $result['message']
                ]);
                
                session()->flash('error', 'Gagal membuat transaksi pembayaran: ' . $result['message']);
                $this->dispatchBrowserEvent('hide-payment-modal');
                return redirect()->back();
            }

        } catch (\Exception $e) {
            Log::error('Error in payWithMidtrans', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
            $this->dispatchBrowserEvent('hide-payment-modal');
            return redirect()->back();
        }
    }

    public function payWithQrisGopay()
    {
        try {
            if (!$this->purchase_id) {
                session()->flash('error', 'Data pembayaran tidak ditemukan.');
                return redirect()->back();
            }

            $purchase = InternetCustomerPurchase::findOrFail($this->purchase_id);
            $internetCustomer = $purchase->customer;

            $midtransService = new MidtransService($internetCustomer->company_id);

            if (!$midtransService->isActive()) {
                session()->flash('error', 'Pembayaran QRIS GoPay tidak tersedia untuk saat ini.');
                $this->dispatchBrowserEvent('hide-payment-modal');
                return redirect()->back();
            }

            // Calculate period
            $periodStart = $internetCustomer->userCustomer->start_billing_date
                ? Carbon::parse($internetCustomer->userCustomer->start_billing_date)
                : now();

            $periodEnd = $periodStart->copy()->addMonths($this->payment_months)->subDay();

            // Update purchase with period info
            $purchase->update([
                'payment_months' => $this->payment_months,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'total_before_discount' => $this->subtotal,
                'discount_amount' => $this->discountAmount,
                'amount_before_tax' => $this->amountBeforeTax,
                'tax_rate' => $this->taxRate,
                'tax_amount' => $this->taxAmount,
                'amount_paid' => $this->totalAmount,
            ]);

            Log::info('Creating QRIS GoPay transaction', [
                'purchase_id' => $purchase->id,
                'customer_id' => $internetCustomer->id,
                'company_id' => $internetCustomer->company_id,
                'payment_months' => $this->payment_months,
                'total_amount' => $this->totalAmount
            ]);

            $result = $midtransService->createTransaction($purchase, $internetCustomer, [
                'payment_months' => $this->payment_months,
                'total_amount' => $this->totalAmount,
                'discount_amount' => $this->discountAmount,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'midtrans_pay_with_ppn' => $this->midtransPayWithPpn
            ]);

            if ($result['success'])
            {
                $purchase->update([
                    'midtrans_snap_token' => $result['snap_token'],
                    'midtrans_transaction_id' => $result['order_id'],
                    'payment_method' => 'midtrans',
                    'midtrans_raw_response' => $result['raw_response'],
                ]);

                Log::info('QRIS GoPay transaction created successfully', [
                    'order_id' => $result['order_id'],
                    'snap_token' => $result['snap_token']
                ]);

                $this->dispatchBrowserEvent('hide-payment-modal');
                return redirect()->away($result['redirect_url'] . '#/gopay-qris');

            } else {
                Log::error('Failed to create QRIS GoPay transaction', [
                    'message' => $result['message']
                ]);

                session()->flash('error', 'Gagal membuat transaksi pembayaran: ' . $result['message']);
                $this->dispatchBrowserEvent('hide-payment-modal');
                return redirect()->back();
            }

        } catch (\Exception $e) {
            Log::error('Error in payWithQrisGopay', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
            $this->dispatchBrowserEvent('hide-payment-modal');
            return redirect()->back();
        }
    }

    public function submitPaymentProof()
    {
        Log::info('submitPaymentProof called', [
            'purchase_id' => $this->purchase_id,
            'has_payment_proof' => !is_null($this->payment_proof),
            'payment_proof_path' => $this->payment_proof ? gettype($this->payment_proof) : null,
            'transfer_date' => $this->transfer_date,
            'transfer_from_bank' => $this->transfer_from_bank,
        ]);

        try {
            // Check if payment proof exists (uploaded via Livewire)
            if (!$this->payment_proof) {
                throw new \Exception('Bukti pembayaran belum diupload');
            }
            
            // Manual validation with specific rules
            $this->validate([
                'transfer_date' => 'required|date|before_or_equal:today',
                'transfer_from_bank' => 'nullable|string|max:255',
                'transfer_from_account_name' => 'nullable|string|max:255',
                'transfer_notes' => 'nullable|string|max:500',
            ]);
            
            Log::info('Validation passed, processing payment...');

            $purchase = InternetCustomerPurchase::findOrFail($this->purchase_id);
            $internetCustomer = $purchase->customer;

            if($internetCustomer->status == ParamSchema::SUSPENDED) {
                dispatch(new ProvisionCustomerJob($internetCustomer->id));
            }

            $internetCustomer->update([
                'status' => ParamSchema::WAITING_PAYMENT_CONFIRMATION
            ]);

            // Calculate period
            $periodStart = $internetCustomer->userCustomer->start_billing_date 
                ? Carbon::parse($internetCustomer->userCustomer->start_billing_date)
                : now();

            $periodEnd = $periodStart->copy()->addMonths($this->payment_months)->subDay();
            
            // Store the file
            $path = $this->payment_proof->store('payment_proofs');
            
            // Update purchase record
            $purchase->update([
                'payment_proof' => $path,
                'payment_method' => 'transfer',
                'payment_months' => $this->payment_months,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'total_before_discount' => $this->subtotal,
                'discount_amount' => $this->discountAmount,
                'amount_paid' => $this->totalAmount,
                'transfer_date' => $this->transfer_date,
                'transfer_from_bank' => $this->transfer_from_bank,
                'transfer_from_account_name' => $this->transfer_from_account_name,
                'transfer_notes' => $this->transfer_notes,
            ]);

            // Notify finance team
            $this->notifyFinanceTeam($internetCustomer);

            $this->reset(['payment_proof', 'transfer_date', 'transfer_from_bank', 'transfer_from_account_name', 'transfer_notes']);
            $this->dispatchBrowserEvent('hide-payment-modal');
            
            return redirect()->back()->with('success', 'Bukti pembayaran berhasil dikirim dan sedang menunggu konfirmasi.');            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation failed - Livewire will automatically show error messages
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error submitting payment proof', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Dispatch error to frontend
            $this->dispatchBrowserEvent('payment-error', [
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
            
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    protected function notifyFinanceTeam($internetCustomer)
    {
        $userFinance = User::whereHas('role.permissions', function ($q) {
            $q->where('method', 'as_finance')
              ->where('table', 'internet_customers');
        })
        ->where(function ($q) use ($internetCustomer) {
            $q->where('company_id', $internetCustomer->company_id)
              ->orWhereHas('accessibleCompanies', function ($sub) use ($internetCustomer) {
                  $sub->where('companies.id', $internetCustomer->company_id);
              });
        })->get();

        if($userFinance->isNotEmpty()) {
            $from = User::where('company_id', $internetCustomer->company_id)
                ->whereHas('role', function ($q) {
                    $q->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN]);
                })
                ->first();
            
            $message = "Pelanggan dengan kode ".$internetCustomer->code." telah mengirim bukti pembayaran untuk {$this->payment_months} bulan. Silakan verifikasi.";
            $directUrl = route('internet-customer.show', $internetCustomer->id);
            
            foreach($userFinance as $finance) {
                $this->sentInbox($finance->id, $from->id, $message, $directUrl);
            }   
        }
    }

    public function viewPaymentProof($purchaseId)
    {
        $purchase = InternetCustomerPurchase::find($purchaseId);
        $this->paymentProofUrl = $purchase->payment_proof ? s3_asset(true,10,$purchase->payment_proof) : null;
        
        if ($this->paymentProofUrl) {
            $this->dispatchBrowserEvent('showImageModal', [
                'title' => 'Bukti Pembayaran ' . $purchase->period_formatted,
                'imageUrl' => $this->paymentProofUrl,
                'transferDetails' => [
                    'date' => $purchase->transfer_date ? \Carbon\Carbon::parse($purchase->transfer_date)->format('d M Y') : null,
                    'bank' => $purchase->transfer_from_bank,
                    'account_name' => $purchase->transfer_from_account_name,
                    'notes' => $purchase->transfer_notes
                ]
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
        $purchases = $this->customer->purchases()
            ->orderBy('created_at', 'desc')
            ->paginate(5);
            
        return view('livewire.internet-customer.internet-customer-show', compact('purchases'))
            ->extends('layouts.app_customer');
    }

    private function sentInbox($to, $from, $message, $directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent($to, $from, $message, $directUrl);
        return true;
    }

    protected function afterPayment($internetPurchase)
    {
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
                $from = User::whereHas('role', function ($query) {
                    $query->whereIn('name', [RoleSchema::SYSTEM_BOS,RoleSchema::ROOT,RoleSchema::FINANCE]);
                })->first();

                foreach($userTechnical as $tech)
                {
                    $this->sentInbox($tech,$from->id,$message, $directUrl);
                }
            }
        }else
        {
            $post['status'] = ParamSchema::REACTIVATED;
            $internetPurchase->customer->update($post);
            
            dispatch(new ProvisionCustomerJob($internetPurchase->customer->id));
            \App\Jobs\SyncInstalledCustomersJob::dispatch([$internetPurchase->customer->id]);
        }

    }
}