<?php

namespace App\Services\Weblas;

use InvalidArgumentException;
use App\Services\Weblas\Device;
class WablasClient
{
    protected $server;
    protected $token;
    protected $secret;
    protected $status;


     public function __construct(?string $server, ?string $token, ?string $secret)
    {
        if (empty($server) || empty($token)) 
        {
            $this->status = false;
            // throw new InvalidArgumentException('Wablas server or token is not set.');
        }else
        {
            $this->server = $server;
            $this->token = $token;
            $this->secret = $secret;
            $this->status = true;
        }

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

    public function status()
    {
        if($this->status)
        {
            $checkDevice = new Device($this);
            return $checkDevice->statusConnect();
        }else
        {
            return false;
        }
    }
}
