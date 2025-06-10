<?php

namespace App\Services\Weblas;

use Illuminate\Support\Facades\Http;


class Schedule
{
    protected $client;

    public function __construct(WablasClient $client)
    {
        $this->client = $client;
    }

    public function new_message($data)
    {
        $payload = [ 'data'=> $data];
        $url = $this->client->api().'v2/schedule';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> $this->client->token()
        ])->post($url,$payload);

        $json_data = $response->json();

        return $json_data;
    }

    public function cancel($id)
    {
        $url = $this->client->api()."schedule-cancel/$id";
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> $this->client->token()
        ])->put($url);
        $json_data = $response->json();

        return $json_data;
    }

    public function delete($id)
    {
        $url = $this->client->api()."schedule/$id";
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> $this->client->token()
        ])->delete($url);
        $json_data = $response->json();

        return $json_data;
    }

}
