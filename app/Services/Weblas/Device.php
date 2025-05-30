<?php

namespace App\Services\Weblas;


use Illuminate\Support\Facades\Http;
use App\Services\Weblas\WablasClient;

class Device
{
    protected $client;

    public function __construct(WablasClient $client)
    {
        $this->client = $client;
    }

    public function info()
    {
        try {
            $url = $this->client->api()."device/info?token=".$this->client->token();
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->get($url);
            $json_data = $response->json();
        } catch (\Throwable $th) 
        {
            throw $th;
        }

        return $json_data ?? 
        [
            'status' => false
        ];
    }

    public function disconnect()
    {
        $url = $this->client->api().'device/disconnect';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> $this->client->token()
        ])->get($url);
        $json_data = $response->json();

        return $json_data;
    }

    public function restart()
    {
        $url = $this->client->api().'device/restart';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> $this->client->token()
        ])->get($url);
        $json_data = $response->json();

        return $json_data;
    }

    public function set_webhook($url)
    {
        $url = $this->client->api().'device/change-webhook-url';
        $data = [
            'webhook_url' => $url
        ];
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> $this->client->token()
        ])->post($url,$data);
        $json_data = $response->json();

        return $json_data;
    }

    public function set_tracking($url)
    {
        $url = $this->client->api().'device/change-tracking-url';
        $data = [
            'tracking_url' => $url
        ];
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> $this->client->token()
        ])->post($url,$data);
        $json_data = $response->json();

        return $json_data;
    }

    public function incoming($set)
    {
        $url = $this->client->api().'device/incoming';
        $data = [
            'incoming' => $set
        ];
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> $this->client->token()
        ])->post($url,$data);
        $json_data = $response->json();

        return $json_data;
    }

    public function statusConnect()
    {
        $data = $this->info();
        if($data['status'])
        {
            return $data['data']['status'] == "connected" ? true : false;
        }else
        {
            return false;
        }

        return false;
    }

}
