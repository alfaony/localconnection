<?php

namespace App\Services;

use LangleyFoxall\XeroLaravel\XeroApp;
use League\OAuth2\Client\Token\AccessToken;
use Dcblogdev\Xero\Facades\Xero;
use Dcblogdev\Xero\Models\XeroToken;
use Illuminate\Support\Facades\Log;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Auth;


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

    public function checkOrCreateContact(array $contactData)
    {   
        $existingContacts = $this->xero->contacts()->where('EmailAddress', $contactData['EmailAddress'])
                                                 ->where('Name', $contactData['Name'])
                                                 ->first();
        if(!$existingContacts)
        {
            $data = [
                'Name' => "testing",
                'EmailAddress' => "testing@gmail.com",
                'FirstName' => "",
                'LastName' => "",
                'Addresses' => [
                    [
                        'AddressType' => 'POBOX',
                        'City' => "asdad",
                        'Region' => "asd"
                    ]
                ]
            ];

            $newContact = Xero::contacts()->store($data);

            return $newContact;
        }

        return $existingContacts;
    }

    public function createInvoice($bast, $contact)
    {
        if (!$contact || !isset($contact->ContactID)) {
            throw new \Exception('Invalid contact. Contact ID is missing.');
        }

        // Prepare the line items for the invoice
        $quoteProduct = $bast->workOrder->quote->quoteProduct;
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
                "Date" => \Carbon\Carbon::now()->format('Y-m-d'), // Current date as invoice date
                "DueDate" => \Carbon\Carbon::now()->addDays(30)->format('Y-m-d'), // Set due date as 30 days from now
                "LineItems" => $lineItems, // Line items array from getLineItems method
                "Status" => "DRAFT", // Invoice status
                "LineAmountTypes" => "Exclusive" // Prices exclusive of tax
            ];
    
            // Call Xero API to create the invoice
            $createdInvoice = Xero::invoices()->store($invoice);

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'invoice' => $createdInvoice
            ], 200);

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
                $lineItems[] = [
                    "Description" => $item->description, // Description of the item/service
                    "Quantity" => $item->qty, // Quantity of the item/service
                    "UnitAmount" => $item->price_sell, // Unit price of the item/service
                    "AccountCode" => '200', // Default Account Code if not provided
                    "TaxType" => "OUTPUT" // Tax type e.g., "OUTPUT"
                ];
            }
        }

        return $lineItems;
    }
}
