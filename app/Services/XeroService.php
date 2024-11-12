<?php

namespace App\Services;

use LangleyFoxall\XeroLaravel\XeroApp;
use League\OAuth2\Client\Token\AccessToken;
use Illuminate\Support\Facades\Log;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Illuminate\Support\Facades\Session;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Schemas\ParamSchema;

use App\Services\XeroBos;
class XeroService
{
    /**
     * Start Xero OAuth2 connection.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected $xero;
    protected $xeroBos;

    /**
     * Constructor to initialize the XeroApp instance.
     */
    // public function __construct()
    // {
    //     $token = XeroToken::first();
    //     if ($token) 
    //     {
    //         $this->xeroBos->setTenantId($token->tenant_id);
    //         if ($this->xeroBos->isConnected()) 
    //         {
    //             $this->xeroBos->getAccessToken($redirectWhenNotConnected = false);
    //         }

            // $this->xero = new XeroApp(
            //     new AccessToken(['access_token' => $token->access_token]),
            //     $token->tenant_id
            // );

    //     }
    // }
    public function __construct()
    {
        $this->xeroBos = new XeroBos();
    }
    protected function setXeroConfig()
    {
        $xeroBos = new XeroBos();

        $access = $xeroBos->getAccessToken();
        if($access)
        {
            $token = $xeroBos->getTokenData();
            if($xeroBos->isConnected())
            {
                $xeroBos->getAccessToken($redirectWhenNotConnected = false);
            }
            
            $this->xero = new XeroApp(
                    new AccessToken(['access_token' => $token->access_token]),
                    $token->tenant_id
                );
        }
    }

    public function isConnected()
    {
        $this->setXeroConfig();
        return $this->xeroBos->isConnected();
    }

    public function connect()
    {
        try {
            $this->setXeroConfig();
            return $this->xeroBos->connect();
        } catch (\Exception $e) {
            Log::error('Xero connection failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to connect to Xero.');
        }
    }

    public function disconnect()
    {
        try {
            $this->setXeroConfig();
            return $this->xeroBos->disconnect();
        } catch (\Exception $e) {
            Log::error('Xero connection failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to connect to Xero.');
        }
    }
    /**
     * Handle the Xero OAuth2 callback.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    // public function handleCallback()
    // {
    //     try {
    //         $this->setXeroConfig();
    //         $this->xeroBos->handleCallback();
    //         return redirect()->route('home')->with('success', 'Connected to Xero successfully!');
    //     } catch (\Exception $e) {
    //         Log::error('Xero callback failed: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'Xero callback failed.');
    //     }
    // }

    public function checkOrCreateContact($customer)
    {   
        $this->setXeroConfig();
        $existingContacts = $this->xero->contacts()->where('EmailAddress', $customer->email)
                                                 ->where('Name', $customer->name)
                                                 ->first();
        if(!$existingContacts)
        {
            $data = [
                'Name' => $customer->name,
                'EmailAddress' => $customer->email,
                'FirstName' => "",
                'LastName' => "",
                'Addresses' => [
                    [
                        'AddressType' => 'POBOX',
                        'City' => "",
                        'Region' => ""
                    ]
                ]
            ];

            $this->xeroBos->contacts()->store($data);
            
            return $this->xero->contacts()->where('EmailAddress', $customer->email)
            ->where('Name', $customer->name)
            ->first();
        }

        return $existingContacts;
    }

    public function createInvoice($invoice, $contact)
    {
        $this->setXeroConfig();
        
        if (!$contact || !isset($contact->ContactID)) {
            $invoice->connecting = false;
            $invoice->save();
            throw new \Exception('Invalid contact. Contact ID is missing.');
        }
        
        // Prepare the line items for the invoice
        $quoteProduct = $invoice->invoiceProducts;
        $lineItems = $this->getLineItems($invoice,$quoteProduct);
        
        if (empty($lineItems)) {
            $invoice->connecting = false;
            $invoice->save();
            throw new \Exception('Line items are empty or invalid.');
        }
        try {
            $invoiceXero = [
                "Type" => "ACCREC",  // ACCREC for sales invoices
                "Contact" => [
                    "ContactID" => $contact->ContactID // Use the contact ID from Xero
                ],
                "Date" => $invoice->start_date,
                "DueDate" => $invoice->end_date,
                "LineItems" => $lineItems, // Line items array from getLineItems method
                "Status" => "DRAFT", // Invoice status
                "LineAmountTypes" => (!empty($invoice->tax) && $invoice->tax > 0) ? "Exclusive" : "NoTax", 
            ];
            
            // Call Xero API to create the invoice
            $response = $this->xeroBos->post('Invoices', $invoiceXero);
            // $response = $this->xeroBos->invoices()->store($invoiceXero);
            
            // Logging API request
            $invoice->connecting = true;
            $invoice->save();
            $this->logApiRequest('invoices', 'POST', $invoice, $response, 200);
            
            return $response ? $response['body']['Invoices'][0] : null;

        } catch (\Exception $e) {
            // dd($e);
            $invoice->connecting = false;
            $invoice->save();
            $this->logApiRequest('invoices', 'POST', $invoice, $e->getMessage(), 500);
            Log::error('Xero invoice creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Xero invoice creation failed.');
        }
    }


    public function updateInvoice($invoice, $status = "DRAFT")
    {
        $this->setXeroConfig();
        // Prepare the line items for the invoice
        $quoteProduct = $invoice->invoiceProducts;

        $lineItems = $this->getLineItems($invoice, $quoteProduct);
        if (!$this->isCheckingInvoice($invoice)) {
            $invoice->connecting = false;
            $invoice->save();
            throw new \Exception('Invoice is not valid.');
        }
        
        try {
            $data = [
                "Type" => "ACCREC",  // ACCREC for sales invoices
                "Contact" => [
                    "ContactID" => $invoice->contact_xero_id // Use the contact ID from Xero
                ],
                "Date" => Carbon::parse($invoice->start_date)->format('Y-m-d'),
                "DueDate" => Carbon::parse($invoice->end_date)->format('Y-m-d'),
                "LineItems" => $lineItems, // Line items array from getLineItems method
                "Status" => $status, // Invoice status
                "LineAmountTypes" => (!empty($invoice->tax) && $invoice->tax > 0) ? "Exclusive" : "NoTax",
            ];
            
            // $response = $this->xeroBos->invoices()->update($invoice->invoice_xero_id, $data);
            $response = $this->xeroBos->post('Invoices/' . $invoice->invoice_xero_id, $data);

            // Logging API request
            $this->logApiRequest('invoices/' . $invoice->invoice_xero_id, 'PUT', $data, $response, 200);

            $invoice->connecting = true;
            $invoice->save();

            return $response;

        } catch (\Exception $e) {
            // dd($e);
            $invoice->connecting = false;
            $invoice->save();
            $this->logApiRequest('invoices/' . $invoice->invoice_xero_id, 'PUT', $data, $e->getMessage(), 500);
            
            Log::error('Xero invoice creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Xero invoice creation failed.');
        }
    }
    public function getInvoice($invoiceId)
    {
        $this->setXeroConfig();
        try {
            $pdfInvoice = $this->xeroBos->get("invoices/{$invoiceId}", null, true, 'application/pdf');
                   // Nama file yang akan digunakan saat mendownload
            $fileName = "invoice_{$invoiceId}.pdf";

            // Mengirimkan file PDF sebagai respons untuk di-download
            return response($pdfInvoice['body'])
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');

            // Mengembalikan path atau URL dari file PDF yang disimpan
        } catch (\Exception $e) {
            Log::error('Xero invoice retrieval failed: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve Xero invoice');
        }
    }

    public function deleteInvoice($invoice)
    {
        $this->setXeroConfig();   
        try {
            if (!$this->isCheckingInvoice($invoice)) 
            {
                throw new \Exception('Invoice is not valid.');
            }
            $data = 
            [
                "InvoiceID"=> $invoice->invoice_xero_id,
                "Status" => $invoice->status == ParamSchema::AUTHORISED ? "VOIDED" : "DELETED"
            ];

            $response = $this->xeroBos->invoices()->update($invoice->invoice_xero_id, $data);

            $this->logApiRequest('Invoices', "delete", $invoice, $response, 200);

            return $response;
            
        } catch (\Exception $e) {
            // dd($e);
            $invoice->connecting = false;
            $invoice->save();
            $this->logApiRequest('invoices/' . $invoice->invoice_xero_id, "delete", $data, $e->getMessage(), 500);

            Log::error('Xero invoice deletion failed: ' . $e->getMessage());
            throw new \Exception('Failed to delete Xero invoice');
        }
    }

    public function findInvoice($invoiceId)
    {
        $this->setXeroConfig();
        return $this->xeroBos->invoices()->find($invoiceId);
    }

    protected function getLineItems($invoice, $quoteProduct)
    {
        $lineItems = [];
        $postItems = [];
        
        if($quoteProduct)
        {
            foreach($quoteProduct as $item)
            {
                if(!$item->product->xero_code)
                {
                    // Mendapatkan nilai max dari row $product->number
                    $maxNumber = Product::byCompany($invoice->userCreate->company_id)->max('number');
                    $nextNumber = $maxNumber ? $maxNumber + 1 : 1;

                    // Membuat kode berdasarkan nama perusahaan user dan nomor produk
                    $name = explode(" ",$item->product->name);
                    $xeroCode = Str::slug($invoice->userCreate->company->name . "-" . $nextNumber . "-" . $name[0]);

                    $xeroChecking = $this->isCheckingItem($xeroCode);

                    $item->product->number = $nextNumber;
                    $item->product->xero_code = $xeroChecking;
                    $item->product->save();

                    $postItems[] = 
                    [
                        "ItemID" => $item->product->id,
                        "Code" => $xeroCode,
                        "Description" => Str::limit(strip_tags($item->product->description),350,''),
                        "Name"=> Str::limit($item->product->name,45,''),
                        "PurchaseDetails"=> [
                                "UnitPrice"=> $item->product->price_buy,
                        ],
                        "SalesDetails"=> 
                        [
                            "UnitPrice"=> $item->product->price_sell,
                        ],
                    ];
                }else{
                    $isExistsItem = $this->xero->items()->where('Code', $item->product->xero_code)->first();
                    if(!$isExistsItem)
                    {
                        $postItems[] = 
                        [
                            "ItemID" => $item->product->id,
                            "Code" => $item->product->xero_code,
                            "Description" => Str::limit(strip_tags($item->product->description),350,''),
                            "Name"=> Str::limit($item->product->name,45,''),
                            "PurchaseDetails"=> [
                                    "UnitPrice"=> $item->product->price_buy,
                            ],
                            "SalesDetails"=> 
                            [
                                "UnitPrice"=> $item->product->price_sell,
                            ],
                        ];
                    }
                }
                $taxType = $this->findOrCreateTaxRate($invoice->tax) ?? "OUTPUT"; // Ambil TaxType berdasarkan nilai pajak di invoice
                // Prepare the line items for the invoice
                $lineItems[] = [
                    "Description" => $item->product->name, // Description of the item/service
                    "Quantity" => $item->qty, // Quantity of the item/service
                    "UnitAmount" => $item->price_sell, // Unit price of the item/service
                    "AccountCode" => '200', // Default Account Code if not provided
                    // "TaxType" => "OUTPUT", // Tax type e.g., "OUTPUT"
                    "TaxType" => $taxType, // Tax type e.g., "OUTPUT"
                    'ItemCode' => $item->product->xero_code,
                ];
            }
        }

        // Calculate the totals including tax, service fee, and discount
        $total = $invoice->invoiceProducts->sum('sub_total');
        $discountAmount = $invoice->discount;
        $chargesAmount = $invoice->charges;
        $totalAll = ($total + $chargesAmount) - $discountAmount;
        $serviceFeeAmount = $invoice->service_fee != 0 ? round(($totalAll * $invoice->service_fee) / 100) : 0;

        // Add service fee, charges, and discount as additional line items in Xero invoice
        if ($serviceFeeAmount > 0) 
        {
            $lineItems[] = [
                "Description" => "Service Fee",
                "Quantity" => 1,
                "UnitAmount" => $serviceFeeAmount,
                "AccountCode" => '200',
                // "TaxType" => "OUTPUT"
                "TaxType" => $taxType, // Tax type e.g., "OUTPUT"
            ];
        }

        if ($chargesAmount > 0) {
            $lineItems[] = [
                "Description" => "Additional Charges",
                "Quantity" => 1,
                "UnitAmount" => $chargesAmount,
                "AccountCode" => '200',
                // "TaxType" => "OUTPUT"
                "TaxType" => $taxType, // Tax type e.g., "OUTPUT"
            ];
        }

        if ($discountAmount > 0) 
        {
            $lineItems[] = [
                "Description" => "Discount",
                "Quantity" => 1,
                "UnitAmount" => -$discountAmount, // Discounts should be negative values
                "AccountCode" => '200',
                // "TaxType" => "OUTPUT"
                "TaxType" => $taxType, // Tax type e.g., "OUTPUT"
            ];
        }

        if(count($postItems) != 0)
        {
            $this->createProductItem($postItems);       
        }
        
        return $lineItems;
    }

    
    protected function createProductItem($postItems)
    {
        try {          
            $data['Items'] = $postItems;
            $response = $this->xeroBos->post('items', $data);
            
            // Logging API request
            $this->logApiRequest('items', 'POST', $data, $response, 200);

            return $response;
        } catch (\Exception $e) {
            // Handle any errors
            $this->logApiRequest('items', 'POST', $data, $e->getMessage(), 500);

            Log::error('Xero item creation failed: ' . $e->getMessage());
            throw new \Exception('Failed to find or create Xero item');
        }
    }
    
    protected function isCheckingInvoice($invoice)
    {
        // First, attempt to find the product in Xero based on its description or another unique field
        $existingInvoice = $this->xero->invoices()
        ->where('InvoiceID', $invoice->invoice_xero_id)
        ->first();

        if($existingInvoice)
        {
            return true;
        }else
        {
            return false;
        }
    }

    protected function isCheckingItem($code)
    {
        do {
            // Cek apakah item dengan kode tersebut sudah ada di Xero
            $item = $this->xero->items()->where('Code', $code)->first();

            // Jika item ditemukan, tambahkan karakter "-" diikuti angka acak pada kode
            if ($item) {
                $code .= '-' . rand(0, 9999); // Tambah angka acak dari 0 sampai 9999
            }
        } while ($item); // Ulangi sampai tidak ada item dengan kode tersebut

        // Setelah kode unik ditemukan, lanjutkan logika untuk membuat item baru dengan kode tersebut
        return $code; // Kembalikan kode yang sudah dipastikan unik
    }

    protected function logApiRequest($endpoint, $method, $requestPayload, $responsePayload, $statusCode)
    {
        ApiLog::create([
            'user_id' => Auth::id(),
            'endpoint' => $endpoint,
            'method' => $method,
            'request_payload' => json_encode($requestPayload),
            'response_payload' => json_encode($responsePayload),
            'status_code' => $statusCode,
        ]);
    }

    protected function findOrCreateTaxRate($taxPercentage)
    {
        // Coba temukan TaxRate yang ada dengan tarif pajak yang diminta
        $taxRatesResponse = $this->xeroBos->get('taxRates');
        $taxRates = $taxRatesResponse['body']['TaxRates'];
    
        // Periksa apakah ada TaxRate dengan EffectiveRate yang sesuai
         foreach ($taxRates as $taxRate) 
         {
            if ($taxRate['EffectiveRate'] == $taxPercentage && $taxRate['Status'] == 'ACTIVE') {
                foreach ($taxRate['TaxComponents'] as $component) {
                    if ($component['Rate'] == $taxPercentage && !$component['IsCompound'] && !$component['IsNonRecoverable']) {
                        return $taxRate['TaxType'];
                    }
                }
            }
        }
        $newTaxRate = [
            'Name' => "Custom Tax Rate {$taxPercentage}%",
            'TaxComponents' => [
                [
                    'Name' => "Custom Tax Component {$taxPercentage}%",
                    'Rate' => $taxPercentage,
                    'IsCompound' => false,
                    'IsNonRecoverable' => false,
                ]
            ]
        ];
        
        try {
            $response = $this->xeroBos->post('taxRates', $newTaxRate);
            if(isset($response['body']['TaxRates']))
            {
                return $response['body']['TaxRates'][0]['TaxType'];
            }
        } catch (\Exception $e) {
            Log::error("Failed to create Tax Rate: " . $e->getMessage());
            throw new \Exception('Failed to create Tax Rate in Xero');
        }
        
    }

}
