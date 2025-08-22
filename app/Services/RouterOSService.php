<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use Illuminate\Support\Facades\Crypt;
use App\Models\Router;
use App\Models\InternetCustomer;
use App\Models\InternetPackage;

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

    public function ensurePppProfile(Client $c, InternetPackage $pkg, string $rosProfile, ?string $fupProfile = null): void
    {
        // cek profile utama
        $exist = $c->query((new Query('/ppp/profile/print'))->where('name', $rosProfile))->read();
        if (empty($exist)) {
            $rate = sprintf('%dM/%dM', $pkg->rate_down_mbps ?? $pkg->bandwidth, $pkg->rate_up_mbps ?? max(1, (int)ceil(($pkg->bandwidth ?? 1) * 0.2)));
            $c->query((new Query('/ppp/profile/add'))
                ->equal('name', $rosProfile)
                ->equal('only-one', 'yes')
                ->equal('rate-limit', $rate)
            )->read();
        }
        // cek FUP profile (opsional)
        if ($fupProfile) {
            $existF = $c->query((new Query('/ppp/profile/print'))->where('name', $fupProfile))->read();
            if (empty($existF)) {
                $rateF = sprintf('%dM/%dM', $pkg->fup_rate_down_mbps ?? 1, $pkg->fup_rate_up_mbps ?? 1);
                $c->query((new Query('/ppp/profile/add'))
                    ->equal('name', $fupProfile)
                    ->equal('only-one', 'yes')
                    ->equal('rate-limit', $rateF)
                )->read();
            }
        }
    }

    public function upsertPppSecret(Client $c, InternetCustomer $cust, string $profile): void
    {
        $q = (new Query('/ppp/secret/print'))->where('name', $cust->username);
        $row = $c->query($q)->read()[0] ?? null;

        if (!$row) {
            $c->query((new Query('/ppp/secret/add'))
                ->equal('name', $cust->username)
                ->equal('password', $this->plainFromHash($cust->pass_hash) ?? 'Temp123!')
                ->equal('service', 'pppoe')
                ->equal('profile', $profile)
                ->equal('comment', $cust->ros_comment_uuid ?? ('uuid:' . $cust->id))
            )->read();
        } else {
            $c->query((new Query('/ppp/secret/set'))
                ->equal('.id', $row['.id'])
                ->equal('profile', $profile)
                ->equal('disabled', $cust->status === 'suspended' ? 'yes' : 'no')
            )->read();
        }
    }

    public function disconnectIfActive(Client $c, ?string $username): void
    {
        if (!$username) return; // no-op kalau belum ada username
        $actives = $c->query((new Query('/ppp/active/print'))->where('name', $username))->read();
        foreach ($actives as $a) {
            $c->query((new Query('/ppp/active/remove'))->equal('.id', $a['.id']))->read();
        }
    }

    private function plainFromHash(?string $hash): ?string
    {
        // Kalau kamu simpan plaintext terenkripsi: return Crypt::decryptString($hash);
        // Kalau benar2 hash bcrypt, MikroTik perlu plaintext — kirim via payload job.
        return null;
    }
}