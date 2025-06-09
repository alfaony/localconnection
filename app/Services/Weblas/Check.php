<?php

namespace App\Services\Weblas;;


use Illuminate\Support\Facades\Http;

class Check
{
     protected $client;

    public function __construct(WablasClient $client)
    {
        $this->client = $client;
    }

    public function phone($phones)
    {
        $url = "https://phone.wablas.com/check-phone-number?phones=$phones";
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> $this->client->token(),
            'Url'=> self::host()
        ])->get($url);
        $json_data = $response->json();

        return $json_data;
    }
}
