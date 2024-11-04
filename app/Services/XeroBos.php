<?php

namespace App\Services;

use Dcblogdev\Xero\Actions\StoreTokenAction;
use Dcblogdev\Xero\Models\XeroToken;
use App\Models\SettingCompany;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use RuntimeException;

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

    protected function setCompanyConfig(): void
    {
        $companyId = Auth::user()->company_id;
        $this->clientId = SettingCompany::byCompany($companyId)->where('field_title', 'client_id')->value('field_value');
        $this->clientSecret = SettingCompany::byCompany($companyId)->where('field_title', 'client_secret')->value('field_value');
        $this->redirectUri = config('xero.redirectUri');

        if (!$this->clientId || !$this->clientSecret || !$this->redirectUri) {
            throw new Exception("Xero credentials not found for company ID {$companyId}");
        }
    }

    public function isConnected(): bool
    {
        $this->setCompanyConfig();
        return !($this->getTokenData() === null);
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

        $where = ['tenant_id' => $tenantId ?? $data['tenant_id'], 'company_id' => $companyId];
        $data = array_merge($data, $tenantData);

        return XeroToken::updateOrCreate($where, $data);
    }
}
