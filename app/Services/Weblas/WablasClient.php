<?php

namespace App\Services\Weblas;

use InvalidArgumentException;
class WablasClient
{
    protected $server;
    protected $token;
    protected $secret;


     public function __construct(?string $server, ?string $token, ?string $secret)
    {
        if (empty($server) || empty($token)) {
            throw new InvalidArgumentException('Wablas server or token is not set.');
        }

        $this->server = $server;
        $this->token = $token;
        $this->secret = $secret;
    }

    public function api()
    {
        $url = "https://$this->server.wablas.com/api/";
        return $url;
    }

    public function token()
    {
        return $this->token;
    }

    public function host()
    {
        $url = "https://$this->server.wablas.com";
        return $url;
    }

    public function secret()
    {
        return $this->secret;
    }

    public function fullToken()
    {
        return $this->token . "." . $this->secret;
    }
}
