<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;

class MikrotikService
{
    protected Client $client;

    public function __construct(?array $override = null)
    {
        $cfg = $override ?: [
            'host'    => config('services.mikrotik.host'),
            'user'    => config('services.mikrotik.user'),
            'pass'    => config('services.mikrotik.pass'),
            'port'    => (int) config('services.mikrotik.port', 8728),
            'ssl'     => (bool) config('services.mikrotik.ssl', false),
            'timeout' => (int) config('services.mikrotik.timeout', 10),
            'attempts'=> 1,
            'legacy'  => false,
        ];
        $this->client = new Client($cfg);
    }

    /** ===== Hotspot User CRUD ===== */

    public function listHotspotUsers(array $filters = []): array
    {
        $q = new Query('/ip/hotspot/user/print');
        foreach (['name','server','profile','disabled'] as $k) {
            if (!empty($filters[$k])) $q->where($k, $filters[$k]);
        }
        return $this->client->query($q)->read();
    }

    public function getHotspotUserById(string $id): ?array
    {
        $q = (new Query('/ip/hotspot/user/print'))->where('.id', $id);
        $res = $this->client->query($q)->read();
        return $res[0] ?? null;
    }

    public function createHotspotUser(array $data): array
    {
        $q = (new Query('/ip/hotspot/user/add'))
            ->equal('server', $data['server'])
            ->equal('name', $data['name'])
            ->equal('password', $data['password']);

        foreach ([
            'profile' => 'profile',
            'comment' => 'comment',
            'limit_uptime' => 'limit-uptime', // e.g. 1d, 12:00:00
            'disabled' => 'disabled',         // yes/no
        ] as $in => $ros) {
            if (isset($data[$in]) && $data[$in] !== null && $data[$in] !== '') {
                $q->equal($ros, $data[$in]);
            }
        }
        return $this->client->query($q)->read();
    }

    public function updateHotspotUser(string $id, array $data): array
    {
        $q = (new Query('/ip/hotspot/user/set'))->equal('.id', $id);

        foreach ($data as $k => $v) {
            if ($v === null) continue;
            $rosKey = match ($k) {
                'limit_uptime' => 'limit-uptime',
                default => str_replace('_','-',$k),
            };
            $q->equal($rosKey, $v);
        }
        return $this->client->query($q)->read();
    }

    public function deleteHotspotUser(string $id): array
    {
        $q = (new Query('/ip/hotspot/user/remove'))->equal('.id', $id);
        return $this->client->query($q)->read();
    }
}