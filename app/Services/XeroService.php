<?php

namespace App\Services;

use LangleyFoxall\XeroLaravel\XeroApp;
use League\OAuth2\Client\Token\AccessToken;
use Dcblogdev\Xero\Facades\Xero;
use Dcblogdev\Xero\Models\XeroToken;
use Illuminate\Support\Facades\Log;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use Illuminate\Support\Str;
class XeroService
{
    /**
     * Start Xero OAuth2 connection.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected $xero;

    /**
     * Constructor to initialize the XeroApp instance.
     */
    public function __construct()
    {
        $token = XeroToken::first();

        if ($token) 
        {
            Xero::setTenantId($token->tenant_id);
            if (Xero::isConnected()) 
            {
                Xero::getAccessToken($redirectWhenNotConnected = false);
            }

            $this->xero = new XeroApp(
                new AccessToken(['access_token' => $token->access_token]),
                $token->tenant_id
            );

        }
    }

    public function connect()
    {
        try {
            return Xero::connect();
        } catch (\Exception $e) {
            Log::error('Xero connection failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to connect to Xero.');
        }
    }

    public function disconnect()
    {
        try {
            return Xero::connect();
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
    public function handleCallback()
    {
        try {
            Xero::callback();
            return redirect()->route('home')->with('success', 'Connected to Xero successfully!');
        } catch (\Exception $e) {
            Log::error('Xero callback failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Xero callback failed.');
        }
    }

    public function checkOrCreateContact($customer)
    {   

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

            $newContact = Xero::contacts()->store($data);
            return $this->xero->contacts()->where('EmailAddress', $customer->email)
            ->where('Name', $customer->name)
            ->first();

            return $newContact;
        }

        return $existingContacts;
    }

    public function createInvoice($invoice, $contact)
    {
        if (!$contact || !isset($contact->ContactID)) {
            throw new \Exception('Invalid contact. Contact ID is missing.');
        }

        // Prepare the line items for the invoice
        $quoteProduct = $invoice->invoiceProducts;
        $lineItems = $this->getLineItems($quoteProduct);
        
        if (empty($lineItems)) {
            throw new \Exception('Line items are empty or invalid.');
        }
        
        try {
            $invoice = [
                "Type" => "ACCREC",  // ACCREC for sales invoices
                "Contact" => [
                    "ContactID" => $contact->ContactID // Use the contact ID from Xero
                ],
                "Date" => $invoice->start_date,
                "DueDate" => $invoice->end_date,
                "LineItems" => $lineItems, // Line items array from getLineItems method
                "Status" => "DRAFT", // Invoice status
                "LineAmountTypes" => "Exclusive" // Prices exclusive of tax
            ];
    
            // Call Xero API to create the invoice
            return Xero::invoices()->store($invoice);

        } catch (\Exception $e) {
            // dd($e);
            Log::error('Xero invoice creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Xero invoice creation failed.');
        }
    }


    public function updateInvoice($invoice, $status = "DRAFT")
    {
        // Prepare the line items for the invoice
        $quoteProduct = $invoice->invoiceProducts;

        $lineItems = $this->getLineItems($quoteProduct);
        if (!$this->isCheckingInvoice($invoice)) {
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
                "LineAmountTypes" => "Exclusive" // Prices exclusive of tax
            ];
            

            return Xero::invoices()->update($invoice->invoice_xero_id,$data);

        } catch (\Exception $e) {
            // dd($e);
            Log::error('Xero invoice creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Xero invoice creation failed.');
        }
    }
    public function getInvoice($invoiceId)
    {
        try {
            $pdfInvoice = Xero::get("invoices/{$invoiceId}", null, true, 'application/pdf');
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

    protected function getLineItems($quoteProduct)
    {
        $lineItems = [];
        $postItems = [];
        if($quoteProduct)
        {
            foreach($quoteProduct as $item)
            {
                $check = $this->isCheckingProduct($item);
                if(!$check)
                {
                    $postItems[] = 
                    [
                        "ItemID" => $item->product->id,
                        "Code" => Str::limit($item->product->slug,15,''),
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

                $lineItems[] = [
                    "Description" => $item->product->name, // Description of the item/service
                    "Quantity" => $item->qty, // Quantity of the item/service
                    "UnitAmount" => $item->price_sell, // Unit price of the item/service
                    "AccountCode" => '200', // Default Account Code if not provided
                    "TaxType" => "OUTPUT", // Tax type e.g., "OUTPUT"
                    'Item' => 
                    [
                        'ItemID' => $item->product->id,
                        "Name"=> Str::limit($item->product->name,45,''),
                        "Code" => Str::limit($item->product->slug,25,''),
                    ]
                ];
            }
        }

        if(count($postItems) != 0)
        {
            $this->findOrCreateItem($item, $postItems);        }

        return $lineItems;
    }

    
    protected function findOrCreateItem($itemData, $postItems)
    {
        try {          
            $data['Items'] = $postItems;

            Xero::post('items', $data);
        } catch (\Exception $e) {
            // Handle any errors
            Log::error('Xero item creation failed: ' . $e->getMessage());
            throw new \Exception('Failed to find or create Xero item');
        }
    }
    

    protected function isCheckingProduct($itemData)
    {
        // First, attempt to find the product in Xero based on its description or another unique field
        $existingItem = $this->xero->items()
        ->where('code', Str::limit($itemData->product->slug,15,''))
        ->first();

        if ($existingItem) 
        {
            return true;
        }else
        {
            return false;
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

}
