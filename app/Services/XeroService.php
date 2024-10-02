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

        if (empty($lineItems)) {
            throw new \Exception('Line items are empty or invalid.');
        }
        
        // dd($status);
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
    public function getLineItems($quoteProduct)
    {
        $lineItems = [];
        if($quoteProduct)
        {
            foreach($quoteProduct as $item)
            {
                $this->findOrCreateItem($item);

                $lineItems[] = [
                    "Description" => $item->product->name, // Description of the item/service
                    "Quantity" => $item->qty, // Quantity of the item/service
                    "UnitAmount" => $item->price_sell, // Unit price of the item/service
                    "AccountCode" => '200', // Default Account Code if not provided
                    "TaxType" => "OUTPUT" // Tax type e.g., "OUTPUT"
                ];
            }
        }

        return $lineItems;
    }

    
    public function findOrCreateItem($itemData)
    {
        try {
            // First, attempt to find the product in Xero based on its description or another unique field
            $existingItem = $this->xero->items()
                ->where('code', $itemData->product->slug)
                ->first();

            // If the item is found, return it
            if ($existingItem) {
                return $existingItem;
            }

            // If the item doesn't exist, create it
            $newItem = [
                "Code" => $itemData->product->slug, // Unique code for the item
                "Name" => $itemData->product->name, // Name of the item
                "Description" => $itemData->product->name, // Description of the item
                "SalesDetails" => [
                    "UnitPrice" => $itemData->product->price_sell, // Unit price for sales
                ],
                "PurchaseDetails" => [
                    "UnitPrice" => $itemData->product->price_buy, // Unit price for purchases
                ]
            ]; 

            return Xero::post('items', $newItem);
        } catch (\Exception $e) {
            dd($e);
            // Handle any errors
            Log::error('Xero item creation failed: ' . $e->getMessage());
            throw new \Exception('Failed to find or create Xero item');
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

}
