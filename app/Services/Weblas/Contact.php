<?php

namespace App\Services\Weblas;

use Illuminate\Support\Facades\Http;


class Contact
{
    protected $client;

    public function __construct(WablasClient $client)
    {
        $this->client = $client;
    }

    public function create($data)
    {
        $payload = [ 'data'=> $data];
        $url = $this->client->api().'v2/create-contact';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> $this->client->token(),
        ])->post($url,$payload);
        $json_data = $response->json();

        return $json_data;
    }
}
