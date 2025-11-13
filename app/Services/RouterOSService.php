<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use Illuminate\Support\Facades\Crypt;
use App\Models\Router;
use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\PppoeServer;
use App\Services\PoolResolver;


class RouterOSService
{
    public function client(Router $router): Client
    {
        return new Client([
            'host'     => $router->host,
            'user'     => $router->username,
            'pass'     => $router->password, // simpan terenkripsi jika mau: Crypt::decryptString(...)
            'port'     => (int)$router->port,
            'ssl'      => (bool)$router->ssl,
            'timeout'  => 5,
            'attempts' => 1,
        ]);
    }

    // helper simple health check sebelum sync berat
    public function quickPing(\RouterOS\Client $c): bool {
        try {
            $c->query(new \RouterOS\Query('/system/identity/print'))->read();
            return true;
        } catch (\Throwable $e) { return false; }
    }

    public function ensurePppProfile(
        Client $c,
        InternetPackage $pkg,
        string $profileName,
        ?string $fupProfileName = null,      // biarkan null kalau tidak pakai FUP
        ?int $routerIdForHints = null,
        ?string $poolNameOverride = null,
        ?string $gatewayOverride = null,
        bool $forceOverwrite = false
    ): void 
    {
        // rate dari paket
        $down = (int)($pkg->rate_down_mbps ?? $pkg->bandwidth ?? 0);
        $up   = (int)($pkg->rate_up_mbps   ?? max(1, (int)ceil(($pkg->bandwidth ?? 1)*0.2)));
        $rate = "{$down}M/{$up}M";

        // cari pool & gateway
        $poolName = $poolNameOverride;
        $gateway  = $gatewayOverride;
        if (!$poolName && $routerIdForHints) {
            // ambil dari PPPoE server pertama di router tsb
            $srv = PppoeServer::with('addressPool')->where('router_id',$routerIdForHints)->first();
            if ($srv?->addressPool) {
                $poolName = $srv->addressPool->name;
                $gateway  = $gateway ?? $srv->addressPool->gateway;
            }
        }

        // optional DNS dari meta paket
        $dnsArr = (array)($pkg->meta['dns_servers'] ?? []);
        $dns    = $dnsArr ? implode(',', $dnsArr) : null;

        // --- ensure PROFILE NORMAL ---
        $this->addOrSetProfile($c, $profileName, $rate, $poolName, $gateway, $dns, $forceOverwrite);

        // --- lewati FUP kalau tidak dipakai ---
        if ($fupProfileName && ($pkg->quota_bytes ?? 0) > 0) {
            $fRate = (int)$pkg->fup_rate_down_mbps.'M/'.(int)$pkg->fup_rate_up_mbps.'M';
            $this->addOrSetProfile($c, $fupProfileName, $fRate, $poolName, $gateway, $dns, $forceOverwrite);
        }
    }

    public function upsertPppSecret(Client $c, InternetCustomer $cust, string $profile, $localAddress = null): void
    {
        $q = (new Query('/ppp/secret/print'))->where('name', $cust->username);
        $row = $c->query($q)->read()[0] ?? null;
        $pwd = trim((string) $cust->pass_hash) ?: 'admin123'; // fallback juga untuk empty string
        
        try {
            if (!$row) {
                $query = (new Query('/ppp/secret/add'))
                    ->equal('name', $cust->username)
                    ->equal('password', $pwd)
                    ->equal('service', 'pppoe')
                    ->equal('profile', $profile)
                    ->equal('comment', $cust->ros_comment_uuid ?? ('uuid:' . $cust->id));

                if (!empty($localAddress)) {
                    $query->equal('local-address', $localAddress);
                }

                $c->query($query)->read();
            } else {
                $id = $row['.id'];
    
                $q = (new Query('/ppp/secret/set'))
                    ->equal('.id', $id)
                    ->equal('name', $cust->username)
                     ->equal('password', $pwd)   
                    ->equal('local-address', $localAddress)  // Update local_address
                    ->equal('profile', $profile);
    
                $c->query($q)->read(); // akan return [] → itu normal
                
            }
        } catch (\Throwable $th) {
            Log::error($th);
            throw $th;
        }

    }


    public function disableSecret(\RouterOS\Client $c, string $username): void
    {
        if (!$username) return;
        $rows = $c->query((new Query('/ppp/secret/print'))->where('name',$username))->read();
        if (empty($rows)) return;

        $c->query(
            (new Query('/ppp/secret/set'))
                ->equal('.id',$rows[0]['.id'])
                ->equal('disabled','yes')
        )->read();
}

    public function disconnectIfActive(Client $c, ?string $username): void
    {
        if (!$username) return; // no-op kalau belum ada username
        $actives = $c->query((new Query('/ppp/active/print'))->where('name', $username))->read();
        foreach ($actives as $a) {
            $c->query((new Query('/ppp/active/remove'))->equal('.id', $a['.id']))->read();
        }
    }

    public function isUserActive(\RouterOS\Client $client, string $username): bool
    {
        if (!$username) return false;

        $res = $client->query(
            (new Query('/ppp/active/print'))->where('name', $username)
        )->read();

        return !empty($res) ?? false;
    }

    private function plainFromHash(?string $hash): ?string
    {
        // Kalau kamu simpan plaintext terenkripsi: return Crypt::decryptString($hash);
        // Kalau benar2 hash bcrypt, MikroTik perlu plaintext — kirim via payload job.
        return null;
    }


    private function addOrSetProfile(
        Client $c,
        string $name,
        string $rate,
        ?string $pool,
        ?string $gw,
        ?string $dns,
        bool $forceOverwrite = false
    ): void {
        $exists = $c->query((new Query('/ppp/profile/print'))->where('name',$name))->read();

        if (empty($exists)) {
            $q = (new Query('/ppp/profile/add'))
                ->equal('name',$name)
                ->equal('only-one','yes')
                ->equal('rate-limit',$rate);
            if ($pool) $q->equal('remote-address',$pool);
            if ($gw)   $q->equal('local-address',$gw);
            if ($dns)  $q->equal('dns-server',$dns);
            $c->query($q)->read();
            return;
        }
        
        $row = $exists[0];
        $q = (new Query('/ppp/profile/set'))
            ->equal('.id',$row['.id'])
            ->equal('only-one','yes')
            ->equal('rate-limit',$rate);

        // kalau forceOverwrite=true → tulis ulang; kalau false → hanya isi kalau kosong
        if ($pool && ($forceOverwrite || empty($row['remote-address']))) $q->equal('remote-address',$pool);
        if ($gw   && ($forceOverwrite || empty($row['local-address'])))  $q->equal('local-address',$gw);
        if ($dns  && ($forceOverwrite || empty($row['dns-server'])))     $q->equal('dns-server',$dns);

        $c->query($q)->read();
    }
}