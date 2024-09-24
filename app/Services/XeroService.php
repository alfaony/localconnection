<?php

namespace App\Services;

use Dcblogdev\Xero\Models\XeroToken;
use LangleyFoxall\XeroLaravel\XeroApp;
use League\OAuth2\Client\Token\AccessToken;
use Dcblogdev\Xero\Facades\Xero;
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
}
