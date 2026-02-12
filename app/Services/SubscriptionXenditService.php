<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
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
        // $this->initializeXendit();
    }

    /**
     * Load settings from database with caching
     */
    protected function loadSettings()
    {
        $cacheKey = "xendit_software_subscription_{$this->companyId}";
        
        $this->settings = Cache::remember($cacheKey, 3600, function () {
            return SettingCompany::byCompany($this->companyId)
                ->where('menu', 'xendit_software_subscription')
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
        return !empty($this->settings['secret_key_software_subscription']) && !empty($this->settings['webhook_token_software_subscription']);
    }

    /**
     * Get environment setting
     */
    public function getEnvironment()
    {
        return $this->settings['environment'] ?? 'development';
    }

    /**
     * Create invoice for subscription payment using Keloola Pay API
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

            // Build items array (plain array for Keloola Pay API)
            $items = [
                [
                    'name' => "{$software->nama} - {$package->nama_paket}",
                    'qty' => 1,
                    'price' => $package->harga,
                    'category' => 'Subscription'
                ]
            ];

            // Create invoice request payload
            $createInvoiceRequestPayload = [
                'external_id' => $subscription->order_number . '_softwareSharing',
                'amount' => $package->harga,
                'description' => $description,
                'items' => $items,
            ];

            // Get Keloola Pay settings
            $baseUrlKeloolaPay = config('services.keloola_pay.base_url');
            $apiKey = $this->settings['secret_key_software_subscription'] ?? null;

            if (!$apiKey) {
                throw new \Exception('Keloola Pay API key not configured');
            }

            // Create invoice using Keloola Pay API
            $url = rtrim($baseUrlKeloolaPay, '/') . '/api/v1/transactions';

            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->asJson()
            ->post($url, $createInvoiceRequestPayload);
                
            // Handle non-success response
            if ($response->failed() || $response->status() !== 201) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $response->body() ?? 'Unknown error';

                Log::error('KeloolaPay subscription invoice creation failed', [
                    'status' => $response->status(),
                    'body' => $errorBody,
                ]);

                throw new \Exception("Failed to create invoice: {$errorMessage}");
            }

            // Extract JSON response safely
            $invoiceData = $response->json();

            // Logging success
            Log::info('KeloolaPay subscription invoice created', [
                'company_id' => $this->companyId ?? null,
                'subscription_id' => $subscription->id ?? null,
                'invoice_id' => $invoiceData['data']['id'] ?? null,
                'external_id' => $subscription->order_number . '_softwareSharing',
                'amount' => $package->harga,
            ]);

            // Return consistent structure
            return [
                'success' => true,
                'invoice' => $invoiceData['data'] ?? $invoiceData,
                'payment_url' => $invoiceData['data']['url_payment'] ? $invoiceData['data']['url_payment'].$apiKey : null,
            ];

        } catch (\Exception $e) {

            Log::error('Xendit subscription invoice creation failed', [
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