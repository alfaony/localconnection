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
    /** Override for xendit_external_id (used on payment retry to avoid UNIQUE violation) */
    protected $externalIdOverride = null;

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
                ->where('menu', 'midtrans_software_sharing')
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
        if (!empty($this->settings['midtrans']['server_key_midtrans_software_sharing']) && 
            !empty($this->settings['midtrans']['client_key_midtrans_software_sharing'])) {
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
     * Calculate PPN (tax) for given amount based on gateway settings
     * 
     * @param float $baseAmount The base amount before PPN
     * @param string $gateway The payment gateway (manual, xendit, midtrans)
     * @return array Array with subtotal, ppn_rate, ppn_amount, and total
     */
    public function calculatePpn($baseAmount, $gateway = 'manual')
    {
        $gatewayAutoAddsPpn = false;

        if ($gateway === 'xendit') {
            $xenditPpnSetting = $this->settings['xendit']['xendit_pay_with_ppn_software_software_subscription'] ?? '0';
            $gatewayAutoAddsPpn = ($xenditPpnSetting === '1' || $xenditPpnSetting === true);
        } elseif ($gateway === 'midtrans') {
            $midtransPpnSetting = $this->settings['midtrans']['midtrans_pay_with_ppn_software_sharing'] ?? '0';
            $gatewayAutoAddsPpn = ($midtransPpnSetting === '1' || $midtransPpnSetting === true);
        }

        // Customer always pays PPN now. Get PPN rate from settings, default to 0 if not set.
        $ppnRate = floatval($this->settings['manual']['ppn_default_software_sharing'] ?? 0);
        
        // Calculate PPN amount
        $ppnAmount = $baseAmount * ($ppnRate / 100);
        
        // Calculate total
        $total = $baseAmount + $ppnAmount;
        
        return [
            'subtotal' => $baseAmount,
            'ppn_rate' => $ppnRate,
            'ppn_amount' => $ppnAmount,
            'total' => $total,
            'gateway_amount' => $gatewayAutoAddsPpn ? $baseAmount : $total,
        ];
    }

    /**
     * Create payment record
     */
    public function createPaymentRecord($data)
    {
        // Use override if set (e.g. retry scenario), otherwise use provided xendit_external_id
        $externalId = $this->externalIdOverride ?? $data['xendit_external_id'];
        // Reset override after use
        $this->externalIdOverride = null;

        return SubscriptionPayment::create([
            'company_id'        => $data['company_id'],
            'software_id'       => $data['software_id'],
            'subscription_id'   => $data['subscription_id'],
            'amount'            => $data['amount'],
            'subtotal'          => $data['subtotal'] ?? null,
            'ppn_rate'          => $data['ppn_rate'] ?? null,
            'ppn_amount'        => $data['ppn_amount'] ?? null,
            'payment_gateway'   => $data['payment_gateway'],
            'payment_method'    => $data['payment_method'] ?? null,
            'xendit_external_id'=> $externalId,
            'status'            => $data['status'] ?? 'pending',
            'expired_at'        => $data['expired_at'] ?? now()->addHours(24),
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

            // Calculate PPN
            $ppnCalculation = $this->calculatePpn($package->harga, 'manual');

            // Create payment record
            $payment = $this->createPaymentRecord([
                'company_id' => $subscription->company_id,
                'software_id' => $subscription->software_id,
                'subscription_id' => $subscription->id,
                'amount' => $ppnCalculation['total'],
                'subtotal' => $ppnCalculation['subtotal'],
                'ppn_rate' => $ppnCalculation['ppn_rate'],
                'ppn_amount' => $ppnCalculation['ppn_amount'],
                'payment_gateway' => 'manual',
                'payment_method' => 'MANUAL_TRANSFER',
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
                'subtotal' => $ppnCalculation['subtotal'],
                'ppn_amount' => $ppnCalculation['ppn_amount'],
                'total' => $ppnCalculation['total'],
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'bank_info' => $bankInfo,
                'ppn_calculation' => $ppnCalculation,
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
            // Calculate PPN
            $ppnCalculation = $this->calculatePpn($package->harga, 'xendit');

            // Create payment record first
            $payment = $this->createPaymentRecord([
                'company_id' => $subscription->company_id,
                'software_id' => $subscription->software_id,
                'subscription_id' => $subscription->id,
                'amount' => $ppnCalculation['total'],
                'subtotal' => $ppnCalculation['subtotal'],
                'ppn_rate' => $ppnCalculation['ppn_rate'],
                'ppn_amount' => $ppnCalculation['ppn_amount'],
                'payment_gateway' => 'xendit',
                'payment_method' => 'XENDIT', // Will be updated by webhook with specific method
                'xendit_external_id' => $subscription->order_number,
                'status' => 'pending',
                'expired_at' => now()->addHours((int) env('SLOT_RESERVATION_HOURS', 1)),
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
                'payment_channel' => $invoiceResult['payment_url'] ?? null,
            ]);

            Log::info('Xendit payment created', [
                'company_id' => $this->companyId,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'invoice_id' => $invoiceResult['invoice']['id'] ?? null,
                'subtotal' => $ppnCalculation['subtotal'],
                'ppn_amount' => $ppnCalculation['ppn_amount'],
                'total' => $ppnCalculation['total'],
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'invoice' => $invoiceResult,
                'ppn_calculation' => $ppnCalculation,
            ];

        } catch (\Exception $e) {
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
            // Calculate PPN
            $ppnCalculation = $this->calculatePpn($package->harga, 'midtrans');

            // Create payment record first
            $payment = $this->createPaymentRecord([
                'company_id' => $subscription->company_id,
                'software_id' => $subscription->software_id,
                'subscription_id' => $subscription->id,
                'amount' => $ppnCalculation['total'],
                'subtotal' => $ppnCalculation['subtotal'],
                'ppn_rate' => $ppnCalculation['ppn_rate'],
                'ppn_amount' => $ppnCalculation['ppn_amount'],
                'payment_gateway' => 'midtrans',
                'payment_method' => 'MIDTRANS', // Will be updated by callback with specific method
                'xendit_external_id' => $subscription->order_number,
                'status' => 'pending',
                'expired_at' => now()->addHours((int) env('SLOT_RESERVATION_HOURS', 1)),
            ]);

            // Use MidtransService to create transaction
            $midtransService = new MidtransService($this->companyId);
            
            if (!$midtransService->isActive()) {
                throw new \Exception('Midtrans payment gateway is not configured');
            }

            // Prepare transaction data for subscription
            $transactionResult = $midtransService->createTransactionForSubscription($subscription, $package, $user, $payment);

            if (!$transactionResult['success']) {
                throw new \Exception($transactionResult['message']);
            }

            // Update payment with Midtrans details
            $payment->update([
                'midtrans_snap_token' => $transactionResult['snap_token'],
                'midtrans_order_id' => $transactionResult['order_id'],
                'payment_channel' => $transactionResult['redirect_url'] ?? null,
            ]);

            Log::info('Midtrans payment created', [
                'company_id' => $this->companyId,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'order_id' => $transactionResult['order_id'],
                'subtotal' => $ppnCalculation['subtotal'],
                'ppn_amount' => $ppnCalculation['ppn_amount'],
                'total' => $ppnCalculation['total'],
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'transaction' => $transactionResult,
                'ppn_calculation' => $ppnCalculation,
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

    /**
     * Expire/void all pending payments for a subscription.
     * Called before creating a fresh payment attempt.
     */
    public function voidPendingPayments($subscription): void
    {
        SubscriptionPayment::where('subscription_id', $subscription->id)
            ->whereIn('status', ['pending', 'unpaid'])
            ->update([
                'status'     => 'expired',
                'expired_at' => now(),
            ]);

        Log::info('Voided pending payments for subscription', [
            'subscription_id' => $subscription->id,
            'order_number'    => $subscription->order_number,
        ]);
    }

    /**
     * Retry a payment for an existing subscription (fresh gateway transaction).
     * Voids the old pending payment and creates a new one.
     * Subscription slot is NOT released — it stays reserved.
     */
    public function retryGatewayPayment($subscription, $package, $user, string $gateway)
    {
        // Void previous pending payments first
        $this->voidPendingPayments($subscription);

        // Generate unique external ID: order_number + retry suffix to avoid UNIQUE violation
        $retryCount = SubscriptionPayment::where('subscription_id', $subscription->id)->count();
        $this->externalIdOverride = $subscription->order_number . '-R' . $retryCount;

        switch ($gateway) {
            case 'xendit':
                return $this->processXenditPayment($subscription, $package, $user);

            case 'midtrans':
                return $this->processMidtransPayment($subscription, $package, $user);

            case 'manual':
                $banks = $this->getManualTransferBanks();
                if (empty($banks)) {
                    return ['success' => false, 'message' => 'Tidak ada bank tersedia untuk transfer manual.'];
                }
                return $this->processManualTransfer($subscription, $package, 0);

            default:
                return ['success' => false, 'message' => 'Gateway tidak dikenal: ' . $gateway];
        }
    }
}
