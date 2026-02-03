<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\SettingCompany;
use App\Models\InternetCustomerPurchase;
use Illuminate\Support\Facades\Cache;
use Midtrans;

class MidtransService
{
    protected $companyId;
    protected $settings;
    protected $serverKey;
    protected $clientKey;
    protected $isProduction;

    public function __construct($companyId = null)
    {
        $this->companyId = $companyId;
        $this->loadSettings();
        $this->initializeMidtrans();
    }

    /**
     * Load settings from database with caching
     */
    protected function loadSettings()
    {
        $cacheKey = "midtrans_settings_{$this->companyId}";

        $this->clearCache($this->companyId);
        
        // Cache for 1 hour (3600 seconds)
        $this->settings = Cache::remember($cacheKey, 3600, function () {
            return SettingCompany::byCompany($this->companyId)
                ->where('menu', 'midtrans_internet_customer')
                ->get()
                ->pluck('field_value', 'field_title')
                ->toArray();
        });
        
    }

    /**
     * Initialize Midtrans with credentials from database
     */
    protected function initializeMidtrans()
    {
        $this->serverKey = $this->settings['server_key_midtrans'] ?? null;
        $this->clientKey = $this->settings['client_key_midtrans'] ?? null;
        $this->isProduction = ($this->settings['environment_midtrans'] ?? 'sandbox') === 'production';

        \Midtrans\Config::$serverKey = $this->serverKey;
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = $this->isProduction == 'production' ? true : false;
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = true;
    }

    /**
     * Check if Midtrans is active for this company
     */
    public function isActive()
    {
        return !empty($this->serverKey) && !empty($this->clientKey);
    }

    /**
     * Get environment setting
     */
    public function getEnvironment()
    {
        return $this->isProduction ? 'production' : 'sandbox';
    }

    /**
     * Get Midtrans API base URL
     */
    protected function getApiUrl()
    {
        return $this->isProduction 
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Get Midtrans redirect URL
     */
    protected function getRedirectUrl($snapToken)
    {
        $baseUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v2/vtweb/'
            : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/';
        
        return $baseUrl . $snapToken;
    }

    /**
     * Create SNAP transaction for customer payment
     */
    public function createTransaction($purchase, $customer, $options = [])
    {
        if (!$this->isActive()) {
            return [
                'success' => false,
                'message' => 'Midtrans payment is not active for this company'
            ];
        }

        try {
            // Extract options
            $paymentMonths = $options['payment_months'] ?? 1;
            
            // Determine price based on PPN setting
            $midtransPayWithPpn = $options['midtrans_pay_with_ppn'] ?? false;
            $defaultPrice = $midtransPayWithPpn 
                ? $customer->internetPackage->price      // PPN enabled: use gross price
                : $customer->internetPackage->price_nett; // PPN disabled: use nett price
            
            $totalAmount = $options['total_amount'] ?? $defaultPrice;
            $discountAmount = $options['discount_amount'] ?? 0;
            $periodStart = $options['period_start'] ?? null;
            $periodEnd = $options['period_end'] ?? null;



            // Build order ID (unique)
            $orderId = 'INT-' . $purchase->id . '-' . time();

            // Build item details FIRST
            $items = [];
            
            // Main package item
            $packageItemName = $customer->internetPackage->name . " ({$paymentMonths} bulan)";
            if ($periodStart && $periodEnd) {
                $packageItemName .= " | " . $periodStart->format('M Y') . " - " . $periodEnd->format('M Y');
            }

            $itemPrice = $midtransPayWithPpn 
                ? $customer->internetPackage->price      // PPN enabled: use gross price
                : $customer->internetPackage->price_nett; // PPN disabled: use nett price
            
            // Round to integer to avoid decimal issues
            $itemPriceRounded = (int) round($itemPrice);
                
            $items[] = [
                'id' => 'PKG-' . $customer->internetPackage->id,
                'price' => $itemPriceRounded,
                'quantity' => $paymentMonths,
                'name' => substr($packageItemName, 0, 50),
            ];

            // Add discount item if applicable
            if ($discountAmount > 0) {
                $discountPercentage = InternetCustomerPurchase::getDiscountPercentage($paymentMonths);
                $discountRounded = (int) round($discountAmount);
                $items[] = [
                    'id' => 'DISC-' . $paymentMonths,
                    'price' => -$discountRounded, // Negative value for discount
                    'quantity' => 1,
                    'name' => "Diskon Pembayaran {$paymentMonths} Bulan ({$discountPercentage}%)",
                ];
            }

            // Calculate gross_amount from sum of items (MUST match exactly)
            $grossAmount = 0;
            foreach ($items as $item) {
                $grossAmount += $item['price'] * $item['quantity'];
            }

            // Build transaction details with calculated gross_amount
            $transactionDetails = [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount, // This MUST equal sum of item_details
            ];

            // Build customer details
            $customerDetails = [
                'first_name' => $customer->name,
                'email' => $customer->userCustomer->email ?? 'noreply@example.com',
                'phone' => $customer->userCustomer->phone_number ?? '',
            ];

            // Build request payload
            $payload = [
                'transaction_details' => $transactionDetails,
                'item_details' => $items,
                'customer_details' => $customerDetails,
                'enabled_payments' => [
                    'credit_card', 'bca_va', 'bni_va', 'bri_va', 'mandiri_va',
                    'permata_va', 'other_va', 'gopay', 'shopeepay', 'qris'
                ],
                'callbacks' => [
                    'finish' => route('internet-customer.customer.show', [
                        'code' => $customer->code,
                        'status' => 'success'
                    ]),
                    'error' => route('internet-customer.customer.show', [
                        'code' => $customer->code,
                        'status' => 'failed'
                    ]),
                    'pending' => route('internet-customer.customer.show', [
                        'code' => $customer->code,
                        'status' => 'pending'
                    ]),
                ],
            ];

            // Log the payload for debugging
            Log::info('Midtrans transaction payload', [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
                'items' => $items,
                'item_sum_validation' => array_sum(array_map(function($item) {
                    return $item['price'] * $item['quantity'];
                }, $items)),
                'customer' => $customer->code,
                'purchase_id' => $purchase->id
            ]);

            // Create SNAP transaction
           $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':'),
            ])->post($this->getApiUrl(), $payload);


            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error_messages'][0] ?? $response->body() ?? 'Unknown error';

                Log::error('Midtrans SNAP transaction failed', [
                    'status' => $response->status(),
                    'body' => $errorBody,
                ]);

                return [
                    'success' => false,
                    'message' => "Failed to create transaction: {$errorMessage}"
                ];
            }

            $result = $response->json();
            $snapToken = $result['token'] ?? null;

            if (!$snapToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to get SNAP token from Midtrans'
                ];
            }

            Log::info('Midtrans SNAP transaction created', [
                'company_id' => $this->companyId,
                'purchase_id' => $purchase->id,
                'order_id' => $orderId,
                'snap_token' => $snapToken,
                'payment_months' => $paymentMonths,
                'total_amount' => $totalAmount,
            ]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'redirect_url' => $this->getRedirectUrl($snapToken),
                'order_id' => $orderId,
                'raw_response' => $result,
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans transaction creation failed', [
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
     * Verify notification from Midtrans
     */
    public function verifyNotification($data)
    {
        $orderId = $data['order_id'] ?? null;
        $statusCode = $data['status_code'] ?? null;
        $grossAmount = $data['gross_amount'] ?? null;
        $signatureKey = $data['signature_key'] ?? null;

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            return false;
        }

        // Verify signature
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        
        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Test connection to Midtrans API
     */
    public function testConnection()
    {
        if (!$this->isActive()) {
            return [
                'success' => false,
                'message' => 'Midtrans credentials not configured'
            ];
        }

        try {
            // Test with minimal transaction
            $params = array(
            'transaction_details' => array(
                'order_id' => rand(),
                'gross_amount' => 10000,
            ),
            'customer_details' => array(
                'first_name' => 'budi',
                'last_name' => 'pratama',
                'email' => 'budi.pra@example.com',
                'phone' => '08111222333',
            ),
        );

        $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':'),
            ])->post($this->getApiUrl(), $params);

            return $response->status() == 201 ? true : false;

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
        Cache::forget("midtrans_settings_{$companyId}");
    }

    /**
     * Get all settings
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
