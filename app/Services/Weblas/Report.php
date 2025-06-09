<?php

namespace App\Services\Weblas;

use Illuminate\Support\Facades\Http;


class Report
{
    protected $client;

    public function __construct(WablasClient $client)
    {
        $this->client = $client;
    }

    public function real_time($data=null)
    {
        $id='';
        $page='';
        if(isset($data['message_id'])){
            $id = $data['message_id'];
        };
        if(isset($data['page'])){
            $page = $data['page'];
        };
        $url = $this->client->api()."report-realtime?message_id=$id&page=$page";
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization'=> $this->client->token()
        ])->get($url);
        $json_data = $response->json();

        return $json_data;
    }
}
