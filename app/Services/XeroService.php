<?php

namespace App\Services;

use Dcblogdev\Xero\Models\XeroToken;
use Illuminate\Support\Facades\Log;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Auth;

use Xero;

class XeroService
{
    /**
     * Start Xero OAuth2 connection.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
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
        try {
            $queryParams = [
                'where' => 'EmailAddress=="' . $contactData['EmailAddress'] . '" || Name=="' . $contactData['Name'] . '"'
            ];

            $contactExists = Xero::contacts()->where('name',)->get();
            dd($contactExists);


            // Log the successful creation of the contact
            ApiLog::create([
                'user_id' => Auth::id(),
                'endpoint' => 'Accounting\\Contact (POST)',
                'method' => 'POST',
                'request_payload' => json_encode($contactData),
                'response_payload' => json_encode($newContact->toArray()),
                'status_code' => 201,
            ]);

            return $newContact;
        } catch (\Exception $e) {
            // Log the error
            dd($e);
            Log::error('Failed to check or create contact in Xero: ' . $e->getMessage());

            ApiLog::create([
                'user_id' => Auth::id(),
                'endpoint' => 'Accounting\\Contact',
                'method' => 'GET/POST',
                'request_payload' => json_encode($contactData),
                'response_payload' => json_encode(['error' => $e->getMessage()]),
                'status_code' => 500,
            ]);

            throw new \Exception('Failed to check or create contact in Xero.');
        }
    }
}
