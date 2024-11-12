<?php

namespace App\Services;

use Dcblogdev\Xero\Actions\StoreTokenAction;
use Dcblogdev\Xero\Models\XeroToken;
use App\Models\SettingCompany;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Dcblogdev\Xero\Actions\tokenExpiredAction;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use RuntimeException;

use Dcblogdev\Xero\Resources\Contacts;
use Dcblogdev\Xero\Resources\CreditNotes;
use Dcblogdev\Xero\Resources\Invoices;

class XeroBos
{
    protected static string $baseUrl = 'https://api.xero.com/api.xro/2.0/';
    protected static string $authorizeUrl = 'https://login.xero.com/identity/connect/authorize';
    protected static string $connectionUrl = 'https://api.xero.com/connections';
    protected static string $tokenUrl = 'https://identity.xero.com/connect/token';
    protected static string $revokeUrl = 'https://identity.xero.com/connect/revocation';

    protected string $tenant_id = '';
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;
    protected string $companyId;

    /**
     * Get the company ID from the session
     * 
     * @return string
     * @throws Exception if company_id is not found in session
     */
    protected function getCompanyId(): string
    {
        $companyId = Session::get('company_id');
        if (!$companyId) {
            throw new Exception("Company ID not found in session");
        }
        return $companyId;
    }
    public function setCompanyPublic($companyId)
    {
        return $this->setCompanyConfig($companyId);
    }
    protected function setCompanyConfig($companyId = null): void
    {
        $this->companyId = $companyId ?? Auth::user()->company_id;
        $this->clientId = SettingCompany::byCompany($this->companyId)->where('field_title', 'client_id')->value('field_value');
        $this->clientSecret = SettingCompany::byCompany($this->companyId)->where('field_title', 'client_secret')->value('field_value');
        $this->redirectUri = config('xero.redirectUri');

        if (!$this->clientId || !$this->clientSecret || !$this->redirectUri) {
            throw new Exception("Xero credentials not found for company ID {$this->companyId}");
        }
    }

    public function contacts(): Contacts
    {
        $this->setCompanyConfig();
        return new Contacts;
    }

    public function creditnotes(): CreditNotes
    {
        $this->setCompanyConfig();
        return new CreditNotes;
    }

    public function invoices(): Invoices
    {
        $this->setCompanyConfig();
        return new Invoices;
    }
    public function isConnected(): bool
    {
        $this->setCompanyConfig();
        return !($this->getTokenData() === null);
    }

    public function __call(string $function, array $args)
    {
        $options = ['get', 'post', 'patch', 'put', 'delete'];
        $path = $args[0] ?? '';
        $data = $args[1] ?? [];
        $raw = $args[2] ?? false;
        $accept = $args[3] ?? 'application/json';
        $headers = $args[4] ?? []; // Add a new line for custom headers

        if (in_array($function, $options)) {
            return $this->guzzle($function, $path, $data, $raw, $accept, $headers);
        } else {
            //request verb is not in the $options array
            throw new RuntimeException($function.' is not a valid HTTP Verb');
        }
    }
    protected function guzzle(string $type, string $request, array $data = [], bool $raw = false, string $accept = 'application/json', array $headers = []): array
    {
        if ($data === []) {
            $data = null;
        }
 
        try {
            $response = Http::withToken($this->getAccessToken())
                ->withHeaders(array_merge(['Xero-tenant-id' => $this->getTenantId()], $headers))
                ->accept($accept)
                ->$type(self::$baseUrl.$request, $data)
                ->throw();

            return [
                'body' => $raw ? $response->body() : $response->json(),
                'headers' => $response->getHeaders(),
            ];
        } catch (RequestException $e) {
            $response = json_decode($e->response->body());
            throw new Exception($response->Detail ?? "Type: $response?->Type Message: $response?->Message Error Number: $response?->ErrorNumber");
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getTenantId(): string
    {
        $token = $this->getTokenData();

        $this->redirectIfNoToken($token);

        return $token->tenant_id;
    }
    
    public function disconnect(): void
    {
        $this->setCompanyConfig();
        try {
            $token = $this->getTokenData();

            Http::withHeaders([
                'authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            ])
                ->asForm()
                ->post(self::$revokeUrl, [
                    'token' => $token->refresh_token,
                ])->throw();

            $token->delete();
        } catch (Exception $e) {
            throw new RuntimeException('Error disconnecting tenant: ' . $e->getMessage());
        }
    }

    public function connect()
    {
        $this->setCompanyConfig();
        
        if (request()->has('code')) 
        {
            try {
                $params = [
                    'grant_type' => 'authorization_code',
                    'code' => request('code'),
                    'redirect_uri' => $this->redirectUri,
                ];

                $result = $this->sendPost(self::$tokenUrl, $params);
                

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $result['access_token'],
                ])
                    ->acceptJson()
                    ->get(self::$connectionUrl)
                    ->throw()
                    ->json();
                
                
                foreach ($response as $tenant) {
                    $tenantData = [
                        'auth_event_id' => $tenant['authEventId'],
                        'tenant_id' => $tenant['tenantId'],
                        'tenant_type' => $tenant['tenantType'],
                        'tenant_name' => $tenant['tenantName'],
                        'created_date_utc' => $tenant['createdDateUtc'],
                        'updated_date_utc' => $tenant['updatedDateUtc'],
                    ];

                    $this->storeToken($result, $tenantData, $tenant['tenantId']);
                }

                return redirect(config('xero.landingUri'));
            } catch (Exception $e) {
                throw new Exception('Error connecting to Xero: ' . $e->getMessage());
            }
        }

        $url = self::$authorizeUrl . '?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => config('xero.scopes'),
        ]);

        return redirect()->away($url);
    }

    public function getTokenData(): ?XeroToken
    {
        $this->setCompanyConfig();
        $companyId = $this->getCompanyId();

        $token = XeroToken::
                        // where('tenant_id', '=', $this->tenant_id)
                            where('company_id', $companyId)
                          ->first();
        if ($token && config('xero.encrypt')) {
            try {
                $token->access_token = Crypt::decryptString($token->access_token);
                $token->refresh_token = Crypt::decryptString($token->refresh_token);
            } catch (DecryptException $e) {
                // Handle decryption error if needed
            }
        }

        return $token;
    }

    public function getAccessToken(bool $redirectWhenNotConnected = true): string
    {
        $this->setCompanyConfig();
        $token = $this->getTokenData();
        $this->redirectIfNoToken($token, $redirectWhenNotConnected);

        if ($token->expires < now()->addMinutes(5)) {
            return $this->renewExpiringToken($token);
        }
        return $token->access_token;
    }

    protected function renewExpiringToken(XeroToken $token): string
    {
        $this->setCompanyConfig();
        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->refresh_token,
            'redirect_uri' => $this->redirectUri,
        ];

        $result = $this->sendPost(self::$tokenUrl, $params);
        
        app(tokenExpiredAction::class)($result, $token);
        $this->storeToken($result, ['tenant_id' => $token->tenant_id]);
        
        return $result['access_token'];
    }

    protected function sendPost(string $url, array $params)
    {
        $this->setCompanyConfig();
        try {
            $response = Http::withHeaders([
                'authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            ])
                ->asForm()
                ->acceptJson()
                ->post($url, $params);

            return $response->json();
        } catch (Exception $e) {
            throw new Exception('Error during POST request: ' . $e->getMessage());
        }
    }

    protected function redirectIfNoToken(?XeroToken $token, bool $redirectWhenNotConnected = true)
    {
        if (!$token && $redirectWhenNotConnected) {
            return redirect()->away($this->redirectUri);
        }
    }

    public function storeToken(array $token, array $tenantData = [], string $tenantId = null): XeroToken
    {
        $this->setCompanyConfig();
        $companyId = $this->getCompanyId();
        $data = [
            'id_token' => $token['id_token'],
            'access_token' => config('xero.encrypt') ? Crypt::encryptString($token['access_token']) : $token['access_token'],
            'expires_in' => $token['expires_in'],
            'token_type' => $token['token_type'],
            'refresh_token' => config('xero.encrypt') ? Crypt::encryptString($token['refresh_token']) : $token['refresh_token'],
            'scopes' => $token['scope'],
            'company_id' => $companyId,
        ];
        if(!isset($tenantId) && !isset($data['tenant_id']))
        {
            $companyId = $this->getCompanyId();
            $tenantIdCheck = XeroToken::where('company_id', $companyId)->first();
            if($companyId && $tenantIdCheck)
            {
                $tenantId = XeroToken::where('company_id', $companyId)->first()->tenant_id;
            }
        }
        $where = [
            'company_id' => $companyId,
            'tenant_id' => $tenantId ?? $data['tenant_id']
        ];
    
        $data = array_merge($data, $tenantData);
    
        // Check if a record already exists for the company
        $existingToken = XeroToken::where('company_id', $companyId)->first();
    
        if ($existingToken) {
            // Update the existing token
            $existingToken->update($data);
            return $existingToken;
        }
    
        // Create a new token if none exists for the company
        return XeroToken::create($data);
    }
}
