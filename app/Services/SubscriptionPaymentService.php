<?php

namespace App\Services;

use App\Models\SubscriptionPayment;
use App\Models\CustomerSubscription;
use App\Models\SettingCompany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SubscriptionPaymentService
{
    protected $companyId;
    protected $settings;

    public function __construct($companyId = null)
    {
        $this->companyId = $companyId;
        $this->loadSettings();
    }

    /**
     * Load all payment gateway settings from database with caching
     */
    protected function loadSettings()
    {
        $cacheKey = "payment_gateway_settings_{$this->companyId}";
        
        $this->settings = Cache::remember($cacheKey, 3600, function () {
            $xenditSettings = SettingCompany::byCompany($this->companyId)
                ->where('menu', 'xendit_software_subscription')
                ->get()
                ->pluck('field_value', 'field_title')
                ->toArray();
            
            $midtransSettings = SettingCompany::byCompany($this->companyId)
                ->where('menu', 'midtrans_software_subscription')
                ->get()
                ->pluck('field_value', 'field_title')
                ->toArray();
            
            $manualSettings = SettingCompany::byCompany($this->companyId)
                ->where('menu', 'software_sharing_setting')
                ->get()
                ->pluck('field_value', 'field_title')
                ->toArray();
            
            return [
                'xendit' => $xenditSettings,
                'midtrans' => $midtransSettings,
                'manual' => $manualSettings,
            ];
        });
    }

    /**
     * Get available payment methods based on settings
     */
    public function getAvailablePaymentMethods()
    {
        $methods = [];

        // Check Manual Transfer
        if (!empty($this->settings['manual']['software_sharing_manual_payment_status']) && 
            $this->settings['manual']['software_sharing_manual_payment_status'] === '1') {
            $methods['manual'] = [
                'name' => 'Manual Transfer',
                'description' => 'Transfer manual ke rekening bank',
                'banks' => $this->getManualTransferBanks(),
            ];
        }

        // Check Xendit (via Keloola Pay)
        if (!empty($this->settings['xendit']['secret_key_software_subscription']) && 
            !empty($this->settings['xendit']['webhook_token_software_subscription'])) {
            $methods['xendit'] = [
                'name' => 'Xendit',
                'description' => 'Pembayaran via Xendit (Virtual Account, E-Wallet, dll)',
            ];
        }

        // Check Midtrans
        if (!empty($this->settings['midtrans']['server_key_software_subscription']) && 
            !empty($this->settings['midtrans']['client_key_software_subscription'])) {
            $methods['midtrans'] = [
                'name' => 'Midtrans',
                'description' => 'Pembayaran via Midtrans (Virtual Account, E-Wallet, dll)',
            ];
        }

        return $methods;
    }

    /**
     * Get manual transfer bank accounts
     */
    public function getManualTransferBanks()
    {
        $banks = [];

        // Bank 1
        if (!empty($this->settings['manual']['bank_name_software_subscription'])) {
            $banks[] = [
                'bank_name' => $this->settings['manual']['bank_name_software_subscription'],
                'account_name' => $this->settings['manual']['account_name_software_subscription'] ?? '',
                'account_number' => $this->settings['manual']['account_number_software_subscription'] ?? '',
            ];
        }

        // Bank 2 (optional)
        if (!empty($this->settings['manual']['software_sharing_atas_nama'])) {
            $banks[] = [
                'bank_name' => $this->settings['manual']['software_sharing_nama_bank'],
                'account_name' => $this->settings['manual']['software_sharing_atas_nama'] ?? '',
                'account_number' => $this->settings['manual']['software_sharing_nama_bank'] ?? '',
            ];
        }

        return $banks;
    }

    /**
     * Create payment record
     */
    public function createPaymentRecord($data)
    {
        return SubscriptionPayment::create([
            'company_id' => $data['company_id'],
            'software_id' => $data['software_id'],
            'subscription_id' => $data['subscription_id'],
            'amount' => $data['amount'],
            'payment_gateway' => $data['payment_gateway'],
            'xendit_external_id' => $data['xendit_external_id'],
            'status' => $data['status'] ?? 'pending',
            'expired_at' => $data['expired_at'] ?? now()->addHours(24),
        ]);
    }

    /**
     * Process manual transfer payment
     */
    public function processManualTransfer($subscription, $package, $selectedBank)
    {
        try {
            $banks = $this->getManualTransferBanks();
            
            if (!isset($banks[$selectedBank])) {
                return [
                    'success' => false,
                    'message' => 'Bank yang dipilih tidak valid'
                ];
            }

            $bankInfo = $banks[$selectedBank];

            // Create payment record
            $payment = $this->createPaymentRecord([
                'company_id' => $subscription->company_id,
                'software_id' => $subscription->software_id,
                'subscription_id' => $subscription->id,
                'amount' => $package->harga,
                'payment_gateway' => 'manual',
                'xendit_external_id' => $subscription->order_number,
                'status' => 'pending',
                'expired_at' => now()->addDays(3), // 3 days for manual transfer
            ]);

            // Update with bank details
            $payment->update([
                'manual_transfer_bank' => $bankInfo['bank_name'],
                'manual_transfer_account_name' => $bankInfo['account_name'],
                'manual_transfer_account_number' => $bankInfo['account_number'],
            ]);

            Log::info('Manual transfer payment created', [
                'company_id' => $this->companyId,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'bank' => $bankInfo['bank_name'],
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'bank_info' => $bankInfo,
            ];

        } catch (\Exception $e) {
            Log::error('Manual transfer payment failed', [
                'company_id' => $this->companyId,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process Xendit payment via Keloola Pay
     */
    public function processXenditPayment($subscription, $package, $user)
    {
        try {
            // Create payment record first
            $payment = $this->createPaymentRecord([
                'company_id' => $subscription->company_id,
                'software_id' => $subscription->software_id,
                'subscription_id' => $subscription->id,
                'amount' => $package->harga,
                'payment_gateway' => 'xendit',
                'xendit_external_id' => $subscription->order_number,
                'status' => 'pending',
                'expired_at' => now()->addHours(24),
            ]);

            // Use SubscriptionXenditService to create invoice
            $xenditService = new SubscriptionXenditService($this->companyId);
            
            if (!$xenditService->isActive()) {
                throw new \Exception('Xendit payment gateway is not configured');
            }

            $invoiceResult = $xenditService->createInvoice($subscription, $package, $user);

            if (!$invoiceResult['success']) {
                throw new \Exception($invoiceResult['message']);
            }

            // Update payment with invoice ID
            $payment->update([
                'xendit_invoice_id' => $invoiceResult['invoice']['id'] ?? null,
            ]);

            Log::info('Xendit payment created', [
                'company_id' => $this->companyId,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'invoice_id' => $invoiceResult['invoice']['id'] ?? null,
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'invoice' => $invoiceResult,
            ];

        } catch (\Exception $e) {
            // dd($e);

            Log::error('Xendit payment failed', [
                'company_id' => $this->companyId,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process Midtrans payment
     */
    public function processMidtransPayment($subscription, $package, $user)
    {
        try {
            // Create payment record first
            $payment = $this->createPaymentRecord([
                'company_id' => $subscription->company_id,
                'software_id' => $subscription->software_id,
                'subscription_id' => $subscription->id,
                'amount' => $package->harga,
                'payment_gateway' => 'midtrans',
                'xendit_external_id' => $subscription->order_number,
                'status' => 'pending',
                'expired_at' => now()->addHours(24),
            ]);

            // Use MidtransService to create transaction
            $midtransService = new MidtransService($this->companyId);
            
            if (!$midtransService->isActive()) {
                throw new \Exception('Midtrans payment gateway is not configured');
            }

            // Prepare transaction data for subscription
            $transactionResult = $midtransService->createTransactionForSubscription($subscription, $package, $user);

            if (!$transactionResult['success']) {
                throw new \Exception($transactionResult['message']);
            }

            // Update payment with Midtrans details
            $payment->update([
                'midtrans_snap_token' => $transactionResult['snap_token'],
                'midtrans_order_id' => $transactionResult['order_id'],
            ]);

            Log::info('Midtrans payment created', [
                'company_id' => $this->companyId,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'order_id' => $transactionResult['order_id'],
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'transaction' => $transactionResult,
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans payment failed', [
                'company_id' => $this->companyId,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload manual transfer proof
     */
    public function uploadTransferProof($payment, $file)
    {
        try {
            if (!$payment->isManualTransfer()) {
                throw new \Exception('Payment is not manual transfer');
            }

            // Store file
            $path = $file->store('manual-transfer-proofs', 'public');

            // Update payment
            $payment->update([
                'manual_transfer_proof' => $path,
            ]);

            Log::info('Transfer proof uploaded', [
                'payment_id' => $payment->id,
                'path' => $path,
            ]);

            return [
                'success' => true,
                'path' => $path,
            ];

        } catch (\Exception $e) {
            Log::error('Upload transfer proof failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear settings cache
     */
    public static function clearCache($companyId)
    {
        Cache::forget("payment_gateway_settings_{$companyId}");
    }
}
