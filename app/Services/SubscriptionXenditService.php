<?php

namespace App\Services;

use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceItem;
use Xendit\Invoice\CustomerObject;
use Xendit\Invoice\NotificationPreference;
use Xendit\Invoice\NotificationChannel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\SettingCompany;
use App\Models\CustomerSubscription;
use App\Models\SubscriptionPayment;

class SubscriptionXenditService
{
    protected $companyId;
    protected $settings;
    protected $invoiceApi;

    public function __construct($companyId = null)
    {
        $this->companyId = $companyId;
        $this->loadSettings();
        $this->initializeXendit();
    }

    /**
     * Load settings from database with caching
     */
    protected function loadSettings()
    {
        $cacheKey = "xendit_subscription_settings_{$this->companyId}";
        
        // Cache untuk 1 jam (3600 detik)
        $this->settings = Cache::remember($cacheKey, 3600, function () {
            return SettingCompany::byCompany($this->companyId)
                ->where('menu', 'xendit_subscription')
                ->get()
                ->pluck('field_value', 'field_title')
                ->toArray();
        });
    }

    /**
     * Initialize Xendit with API key from database
     */
    protected function initializeXendit()
    {
        $secretKey = $this->settings['secret_key'] ?? null;
        
        if (!$secretKey) {
            throw new \Exception('Xendit secret key not configured for this company');
        }

        // Xendit v7.0 way
        Configuration::setXenditKey($secretKey);
        $this->invoiceApi = new InvoiceApi();
    }

    /**
     * Check if Xendit is active for this company
     */
    public function isActive()
    {
        return !empty($this->settings['secret_key']) && !empty($this->settings['webhook_token']);
    }

    /**
     * Get environment setting
     */
    public function getEnvironment()
    {
        return $this->settings['environment'] ?? 'development';
    }

    /**
     * Create invoice for subscription payment
     */
    public function createInvoice($subscription, $package, $customer)
    {
        if (!$this->isActive()) {
            return [
                'success' => false,
                'message' => 'Xendit payment is not active for this company'
            ];
        }

        try {
            $software = $subscription->masterAccount->software;
            
            // Build description
            $description = "Pembayaran {$software->nama} - {$software->tipe_paket}";
            $description .= " untuk {$package->nama_paket}";

            // Setup customer object
            $customerObject = new CustomerObject([
                'given_names' => $customer->name,
                'email' => $customer->email ?? 'noreply@example.com',
                'mobile_number' => $customer->phone ?? '',
            ]);

            // Build items array
            $items = [
                new InvoiceItem([
                    'name' => "{$software->nama} - {$package->nama_paket}",
                    'quantity' => 1,
                    'price' => $package->harga,
                    'category' => 'Subscription'
                ])
            ];

            // Setup notification preferences
            $notificationPreference = new NotificationPreference([
                'invoice_created' => [
                    NotificationChannel::EMAIL
                ],
                'invoice_reminder' => [
                    NotificationChannel::EMAIL
                ],
                'invoice_paid' => [
                    NotificationChannel::EMAIL
                ],
            ]);

            // Get success and failure redirect URLs
            $successUrl = route('customer.payment.success', ['order' => $subscription->order_number]);
            $failureUrl = route('customer.payment.failed', ['order' => $subscription->order_number]);

            // Create invoice request
            $createInvoiceRequest = new CreateInvoiceRequest([
                'external_id' => $subscription->order_number,
                'amount' => $package->harga,
                'description' => $description,
                'invoice_duration' => 86400, // 24 hours
                'customer' => $customerObject,
                'customer_notification_preference' => $notificationPreference,
                'success_redirect_url' => $successUrl,
                'failure_redirect_url' => $failureUrl,
                'currency' => 'IDR',
                'items' => $items,
            ]);

            // Create invoice using API
            $invoice = $this->invoiceApi->createInvoice($createInvoiceRequest);
            
            Log::info('Xendit subscription invoice created', [
                'company_id' => $this->companyId,
                'subscription_id' => $subscription->id,
                'order_number' => $subscription->order_number,
                'invoice_id' => $invoice['id'],
                'amount' => $package->harga,
            ]);

            return [
                'success' => true,
                'invoice' => $invoice
            ];

        } catch (\Xendit\XenditSdkException $e) {
            Log::error('Xendit SDK exception', [
                'company_id' => $this->companyId,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'full_error' => $e->getFullError()
            ]);

            return [
                'success' => false,
                'message' => 'Xendit Error: ' . $e->getMessage()
            ];

        } catch (\Exception $e) {
            Log::error('Xendit invoice creation failed', [
                'company_id' => $this->companyId,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get invoice detail
     */
    public function getInvoice($invoiceId)
    {
        try {
            return $this->invoiceApi->getInvoiceById($invoiceId);
        } catch (\Xendit\XenditSdkException $e) {
            Log::error('Failed to retrieve Xendit invoice', [
                'company_id' => $this->companyId,
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'full_error' => $e->getFullError()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve Xendit invoice', [
                'company_id' => $this->companyId,
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Expire an invoice
     */
    public function expireInvoice($invoiceId)
    {
        try {
            return $this->invoiceApi->expireInvoice($invoiceId);
        } catch (\Xendit\XenditSdkException $e) {
            Log::error('Failed to expire Xendit invoice', [
                'company_id' => $this->companyId,
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to expire Xendit invoice', [
                'company_id' => $this->companyId,
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Verify webhook callback token
     */
    public function verifyCallbackToken($callbackToken)
    {
        $webhookToken = $this->settings['webhook_token'] ?? null;
        return $callbackToken === $webhookToken;
    }

    /**
     * Clear settings cache
     */
    public static function clearCache($companyId)
    {
        Cache::forget("xendit_subscription_settings_{$companyId}");
    }

    /**
     * Get all settings
     */
    public function getSettings()
    {
        return $this->settings;
    }
}