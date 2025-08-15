<?php

// app/Services/MikrotikService.php
namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use App\Helpers\MikrotikConfig;

class MikrotikService {
    protected Client $client;

    public function __construct(private String $companyId) {
        $this->client = new Client(MikrotikConfig::forCompany($this->companyId));
    }

    public function secretFind($name) {
        $q = (new Query('/ppp/secret/print'))->where('name', $name);
        $res = $this->client->query($q)->read();
        return $res[0] ?? null;
    }

    public function secretAdd(array $data) {
        $q = (new Query('/ppp/secret/add'))
            ->equal('name', $data['name'])
            ->equal('password', $data['password'])
            ->equal('service', 'pppoe');

        if (!empty($data['profile'])) $q->equal('profile', $data['profile']);
        if (!empty($data['comment'])) $q->equal('comment', $data['comment']);
        if (isset($data['disabled'])) $q->equal('disabled', $data['disabled']);

        return $this->client->query($q)->read();
    }

    public function secretSet($id, array $data) {
        $q = (new Query('/ppp/secret/set'))->equal('.id', $id);
        foreach ($data as $k => $v) {
            if ($v === null) continue;
            $q->equal(str_replace('_', '-', $k), $v);
        }
        return $this->client->query($q)->read();
    }

    public function activeDisconnectByName($name) {
        $rows = $this->client
            ->query((new Query('/ppp/active/print'))->where('name', $name))
            ->read();
        // dd($rows);
        foreach ($rows as $r) {
            if (!empty($r['.id'])) {
                $this->client
                    ->query((new Query('/ppp/active/remove'))->equal('.id', $r['.id']))
                    ->read();
            }
        }
    }
}