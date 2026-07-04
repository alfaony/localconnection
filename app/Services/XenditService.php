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
use Illuminate\Support\Facades\Http;
use App\Models\SettingCompany;
use App\Models\InternetCustomerPurchase;
use Illuminate\Support\Facades\Cache;

class XenditService
{
    protected $companyId;
    protected $settings;
    protected $invoiceApi;
    protected $baseUrlKeloolaPay;
    protected $apiKey;

    public function __construct($companyId = null)
    {
        $this->companyId = $companyId;
        $this->loadSettings();
        $this->initializeXendit();
        $this->initializeKeloolaPay();
        $this->baseUrlKeloolaPay = config('services.keloola_pay.base_url');
    }

    /**
     * Load settings from database with caching
     */
    protected function loadSettings()
    {
        $cacheKey = "xendit_settings_{$this->companyId}";
        
        // Cache untuk 1 jam (3600 detik)
        $this->settings = Cache::remember($cacheKey, 3600, function () {
            return SettingCompany::byCompany($this->companyId)
                ->where('menu', 'xendit_internet_customer')
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

    protected function initializeKeloolaPay()
    {
        $secretKey = $this->settings['secret_key'] ?? null;
        
        if (!$secretKey) {
            throw new \Exception('Keloola secret key not configured for this company');
        }

        // Xendit v7.0 way
        $this->apiKey = $secretKey;
    }

    /**
     * Check if Xendit is active for this company
     */
    public function isActive()
    {
        return ($this->settings['secret_key'] || $this->settings['public_key'] && $this->settings['webhook_token'] ? true : false);
    }

    /**
     * Get environment setting
     */
    public function getEnvironment()
    {
        return $this->settings['environment'] ?? 'development';
    }

    /**
     * Create Invoice Keloola Pay
     */
    public function createInvoiceKeloolaPay($purchase, $customer, $options = [])
    {
        if (!$this->isActive()) {
            return [
                'success' => false,
                'message' => 'Xendit payment is not active for this company'
            ];
        }

        try {
            // Extract options
            $paymentMonths = $options['payment_months'] ?? 1;
            
            // Determine price based on PPN setting (harga sesuai wilayah customer, fallback ke harga global paket)
            $xenditPayWithPpn = $options['xendit_pay_with_ppn'] ?? false;
            $priceData = $customer->internetPackage->getPriceForRegion(
                $customer->province_id,
                $customer->city_id,
                $customer->district_id,
                $customer->subdistrict_id
            );
            $defaultPrice = $xenditPayWithPpn
                ? $priceData['price']      // PPN enabled: use gross price
                : $priceData['price_nett']; // PPN disabled: use nett price

            $totalAmount = $options['total_amount'] ?? $defaultPrice;
            $discountAmount = $options['discount_amount'] ?? 0;
            $subscriptionPeriod = $options['subscription_period'] ?? null;

            // Build description
            $description = "Pembayaran paket internet {$customer->internetPackage->name}";
            
            if ($subscriptionPeriod) {
                $periodStart = $subscriptionPeriod['start']->format('M Y');
                $periodEnd = $subscriptionPeriod['end']->format('M Y');
                $description .= " | Periode: {$periodStart} - {$periodEnd}";
            } else {
                $description .= " untuk {$paymentMonths} bulan";
            }
            
            if ($discountAmount > 0) {
                $discountPercentage = InternetCustomerPurchase::getDiscountPercentage($paymentMonths);
                $description .= " (Diskon {$discountPercentage}%)";
            }

            // Setup customer object
            $customerObject = new CustomerObject([
                'given_names' => $customer->name,
                'email' => $customer->userCustomer->email ?? 'noreply@example.com',
                'mobile_number' => $customer->userCustomer->phone_number ?? '',
            ]);

            // Build items array
            $items = [];

            // Main package item dengan periode info
            $packageItemName = $customer->internetPackage->name . " ({$paymentMonths} bulan)";
            if ($subscriptionPeriod) {
                $packageItemName .= " | {$subscriptionPeriod['start']->format('M Y')} - {$subscriptionPeriod['end']->format('M Y')}";
            }

            $itemPrice = $xenditPayWithPpn
                ? $priceData['price']      // PPN enabled: use gross price
                : $priceData['price_nett']; // PPN disabled: use nett price
                
            $items[] = 
            [
                'name' => $packageItemName,
                'qty' => $paymentMonths,
                'price' => $itemPrice,
                'category' => 'Internet Package'
            ];

            // Add discount item if applicable
            if ($discountAmount > 0) {
                $discountPercentage = InternetCustomerPurchase::getDiscountPercentage($paymentMonths);
                $items[] = 
                [
                    'name' => "Diskon Pembayaran {$paymentMonths} Bulan ({$discountPercentage}%)",
                    'qty' => 1,
                    'price' => -$discountAmount, // Negative for discount
                    'category' => 'Discount'
                ];
            }

            // Create invoice request
            $createInvoiceRequestPayload = [
                'external_id' => $purchase->id.'_internetCustomer',
                'amount' => $totalAmount, // Total after discount
                'description' => $description,
                'items' => $items,
            ];

            // Create invoice using API
            $url = rtrim($this->baseUrlKeloolaPay, '/') . '/api/v1/transactions';

            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->asJson() // <— penting: ubah content type ke application/json
            ->post($url, $createInvoiceRequestPayload);

            // dd($createInvoiceRequestPayload, $response->body());

            // ✅ Handle non-success response
            if ($response->failed() || $response->status() !== 201) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $response->body() ?? 'Unknown error';

                Log::error('KeloolaPay invoice creation failed', [
                    'status' => $response->status(),
                    'body' => $errorBody,
                ]);

                throw new \Exception("Failed to create invoice: {$errorMessage}");
            }

            // ✅ Extract JSON response safely
            $invoiceData = $response->json();

            // ✅ Logging success
            Log::info('KeloolaPay invoice created', [
                'company_id' => $this->companyId ?? null,
                'purchase_id' => $purchase->id ?? null,
                'invoice_id' => $invoiceData['data']['id'] ?? null,
                'payment_months' => $paymentMonths ?? null,
                'subscription_period' => isset($subscriptionPeriod) ? [
                    'start' => $subscriptionPeriod['start']->format('Y-m-d'),
                    'end' => $subscriptionPeriod['end']->format('Y-m-d')
                ] : null,
                'total_amount' => $totalAmount ?? null,
                'discount_amount' => $discountAmount ?? 0,
            ]);

            // ✅ Return consistent structure
            return [
                'success' => true,
                'status' => $response->status(),
                'data' => $invoiceData['data'] ?? null,
                'invoice' => $invoiceData,
                'token' => $this->apiKey
            ];

        } catch (\Xendit\XenditSdkException $e) {
            // dd($e);
            Log::error('Xendit SDK exception', [
                'company_id' => $this->companyId,
                'purchase_id' => $purchase->id,
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
                'purchase_id' => $purchase->id,
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
     * Create invoice for customer payment with multiple months support
     */
    public function createInvoice($purchase, $customer, $options = [])
    {
        if (!$this->isActive()) {
            return [
                'success' => false,
                'message' => 'Xendit payment is not active for this company'
            ];
        }

        try {
            // Extract options
            $paymentMonths = $options['payment_months'] ?? 1;
            
            // Determine price based on PPN setting (harga sesuai wilayah customer, fallback ke harga global paket)
            $xenditPayWithPpn = $options['xendit_pay_with_ppn'] ?? false;
            $priceData = $customer->internetPackage->getPriceForRegion(
                $customer->province_id,
                $customer->city_id,
                $customer->district_id,
                $customer->subdistrict_id
            );
            $defaultPrice = $xenditPayWithPpn
                ? $priceData['price']      // PPN enabled: use gross price
                : $priceData['price_nett']; // PPN disabled: use nett price

            $totalAmount = $options['total_amount'] ?? $defaultPrice;
            $discountAmount = $options['discount_amount'] ?? 0;
            $subscriptionPeriod = $options['subscription_period'] ?? null;

            // Build description
            $description = "Pembayaran paket internet {$customer->internetPackage->name}";
            
            if ($subscriptionPeriod) {
                $periodStart = $subscriptionPeriod['start']->format('M Y');
                $periodEnd = $subscriptionPeriod['end']->format('M Y');
                $description .= " | Periode: {$periodStart} - {$periodEnd}";
            } else {
                $description .= " untuk {$paymentMonths} bulan";
            }
            
            if ($discountAmount > 0) {
                $discountPercentage = InternetCustomerPurchase::getDiscountPercentage($paymentMonths);
                $description .= " (Diskon {$discountPercentage}%)";
            }

            // Setup customer object
            $customerObject = new CustomerObject([
                'given_names' => $customer->name,
                'email' => $customer->userCustomer->email ?? 'noreply@example.com',
                'mobile_number' => $customer->userCustomer->phone_number ?? '',
            ]);

            // Build items array
            $items = [];

            // Main package item dengan periode info
            $packageItemName = $customer->internetPackage->name . " ({$paymentMonths} bulan)";
            if ($subscriptionPeriod) {
                $packageItemName .= " | {$subscriptionPeriod['start']->format('M Y')} - {$subscriptionPeriod['end']->format('M Y')}";
            }

            $itemPrice = $xenditPayWithPpn
                ? $priceData['price']      // PPN enabled: use gross price
                : $priceData['price_nett']; // PPN disabled: use nett price
                
            $items[] = new InvoiceItem([
                'name' => $packageItemName,
                'quantity' => $paymentMonths,
                'price' => $itemPrice,
                'category' => 'Internet Package'
            ]);

            // Add discount item if applicable
            if ($discountAmount > 0) {
                $discountPercentage = InternetCustomerPurchase::getDiscountPercentage($paymentMonths);
                $items[] = new InvoiceItem([
                    'name' => "Diskon Pembayaran {$paymentMonths} Bulan ({$discountPercentage}%)",
                    'quantity' => 1,
                    'price' => -$discountAmount, // Negative for discount
                    'category' => 'Discount'
                ]);
            }

            // Setup notification preferences
            $notificationPreference = new NotificationPreference([
                'invoice_created' => [
                    NotificationChannel::WHATSAPP,
                    NotificationChannel::SMS,
                    NotificationChannel::EMAIL
                ],
                'invoice_reminder' => [
                    NotificationChannel::WHATSAPP,
                    NotificationChannel::SMS,
                    NotificationChannel::EMAIL
                ],
                'invoice_paid' => [
                    NotificationChannel::WHATSAPP,
                    NotificationChannel::SMS,
                    NotificationChannel::EMAIL
                ],
            ]);

            // Create invoice request
            $createInvoiceRequest = new CreateInvoiceRequest([
                'external_id' => $purchase->id.'_internetCustomer',
                'amount' => $totalAmount, // Total after discount
                'description' => $description,
                'invoice_duration' => 86400 * 3, // 3 days (72 hours)
                'customer' => $customerObject,
                'customer_notification_preference' => $notificationPreference,
                'success_redirect_url' => route('internet-customer.customer.show', [
                    'code' => $customer->code,
                    'status' => 'success'
                ]),
                'failure_redirect_url' => route('internet-customer.customer.show', [
                    'code' => $customer->code,
                    'status' => 'failed'
                ]),
                'currency' => 'IDR',
                'items' => $items,
            ]);

            // Create invoice using API
            $invoice = $this->invoiceApi->createInvoice($createInvoiceRequest);
            
            Log::info('Xendit invoice created', [
                'company_id' => $this->companyId,
                'purchase_id' => $purchase->id,
                'invoice_id' => $invoice['id'],
                'payment_months' => $paymentMonths,
                'subscription_period' => $subscriptionPeriod ? [
                    'start' => $subscriptionPeriod['start']->format('Y-m-d'),
                    'end' => $subscriptionPeriod['end']->format('Y-m-d')
                ] : null,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount
            ]);

            return [
                'success' => true,
                'invoice' => $invoice
            ];

        } catch (\Xendit\XenditSdkException $e) {
            // dd($e);
            Log::error('Xendit SDK exception', [
                'company_id' => $this->companyId,
                'purchase_id' => $purchase->id,
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
                'purchase_id' => $purchase->id,
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
     * Verify webhook signature (more secure option)
     */
    public function verifyWebhookSignature($receivedSignature, $payload)
    {
        $webhookToken = $this->settings['webhook_token'] ?? '';
        
        // Xendit uses HMAC SHA256 for webhook signature
        $calculatedSignature = hash_hmac(
            'sha256',
            json_encode($payload),
            $webhookToken
        );

        return hash_equals($calculatedSignature, $receivedSignature);
    }

    /**
     * Test connection to Xendit API
     */
    public function testConnection()
    {
        try {
            // Create a test invoice with minimal duration
            $testCustomer = new CustomerObject([
                'given_names' => 'Test Connection',
                'email' => 'test@example.com',
            ]);

            $testItem = new InvoiceItem([
                'name' => 'Test Connection Item',
                'quantity' => 1,
                'price' => 10000,
                'category' => 'Test'
            ]);

            $testRequest = new CreateInvoiceRequest([
                'external_id' => 'TEST-' . time(),
                'amount' => 10000,
                'description' => 'Test connection invoice',
                'invoice_duration' => 60, // 1 minute
                'customer' => $testCustomer,
                'currency' => 'IDR',
                'items' => [$testItem],
            ]);

            $testInvoice = $this->invoiceApi->createInvoice($testRequest);
            
            // Immediately expire the test invoice
            if (isset($testInvoice['id'])) {
                $this->invoiceApi->expireInvoice($testInvoice['id']);
            }

            return [
                'success' => true,
                'message' => 'Connection successful',
                'environment' => $this->getEnvironment(),
                'test_invoice_id' => $testInvoice['id'] ?? null
            ];

        } catch (\Xendit\XenditSdkException $e) {
            return [
                'success' => false,
                'message' => 'Xendit SDK Error: ' . $e->getMessage(),
                'full_error' => $e->getFullError()
            ];
        } catch (\Exception $e) {
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
        Cache::forget("xendit_settings_{$companyId}");
    }

    /**
     * Get all settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Get available payment channels
     */
    public function getPaymentChannels()
    {
        return [
            'virtual_accounts' => [
                'BCA', 'BNI', 'BRI', 'MANDIRI', 'PERMATA', 'CIMB'
            ],
            'e_wallets' => [
                'OVO', 'DANA', 'LINKAJA', 'SHOPEEPAY'
            ],
            'retail_outlets' => [
                'ALFAMART', 'INDOMARET'
            ],
            'credit_card' => true,
            'qr_codes' => [
                'QRIS'
            ]
        ];
    }

    /**
     * Format invoice data for display
     */
    public function formatInvoiceData($invoice)
    {
        if (!$invoice) {
            return null;
        }

        return [
            'id' => $invoice['id'] ?? null,
            'external_id' => $invoice['external_id'] ?? null,
            'status' => $invoice['status'] ?? null,
            'amount' => $invoice['amount'] ?? 0,
            'invoice_url' => $invoice['invoice_url'] ?? null,
            'expiry_date' => $invoice['expiry_date'] ?? null,
            'description' => $invoice['description'] ?? null,
            'paid_at' => $invoice['paid_at'] ?? null,
            'payment_channel' => $invoice['payment_channel'] ?? null,
            'payment_method' => $invoice['payment_method'] ?? null,
        ];
    }
}