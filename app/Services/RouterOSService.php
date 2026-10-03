<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use Illuminate\Support\Facades\Crypt;
use App\Models\Router;
use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\PppoeServer;
use App\Models\HotspotVoucher;
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

public function ensureSuspendedPppProfile(Client $c, string $profileName = 'SUSPENDED'): void
{
    $exists = $c->query((new Query('/ppp/profile/print'))->where('name', $profileName))->read();

    if (empty($exists)) {
        $c->query(
            (new Query('/ppp/profile/add'))
                ->equal('name', $profileName)
                ->equal('rate-limit', '2M/2M')
                ->equal('only-one', 'yes')
        )->read();
    }
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
    $q = (new Query('/ppp/secret/print'))->where('comment', $cust->id);
    $row = $c->query($q)->read()[0] ?? null;
    $pwd = trim((string) $cust->pass_hash) ?: 'admin123'; // fallback juga untuk empty string
    
    try {
        if (!$row) {
            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $cust->username)
                ->equal('password', $pwd)
                ->equal('service', 'pppoe')
                ->equal('profile', $profile)
                ->equal('comment', $cust->id);

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
                ->equal('profile', $profile);

            if (!empty($localAddress)) 
            {
                $q->equal('local-address', $localAddress);
            }
            $c->query($q)->read();

            $meta = (array) $cust->meta;
            $meta['ros_secret'] = [
                'id'       => $row['.id'] ?? null,
                'disabled' => "no",
                'profile'  => $profile,
                'comment'  => $row['comment'] ?? null,
            ];
            $cust->meta = $meta;
            $cust->save();
            
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


// ============================================================
// HOTSPOT METHODS
// ============================================================

/**
 * Pastikan hotspot user profile ada di MikroTik.
 * Endpoint: /ip/hotspot/user/profile
 */
public function ensureHotspotUserProfile(
    Client $c,
    string $profileName,
    string $rate,
    ?int $sessionTimeout = null,
    ?int $idleTimeout = null
): void {
    $exists = $c->query((new Query('/ip/hotspot/user/profile/print'))->where('name', $profileName))->read();

    if (empty($exists)) {
        $q = (new Query('/ip/hotspot/user/profile/add'))
            ->equal('name', $profileName)
            ->equal('rate-limit', $rate);
        if ($sessionTimeout > 0) $q->equal('session-timeout', $sessionTimeout . 's');
        if ($idleTimeout > 0)    $q->equal('idle-timeout', $idleTimeout . 's');
        $c->query($q)->read();
        return;
    }

    $q = (new Query('/ip/hotspot/user/profile/set'))
        ->equal('.id', $exists[0]['.id'])
        ->equal('rate-limit', $rate);
    if ($sessionTimeout > 0) $q->equal('session-timeout', $sessionTimeout . 's');
    if ($idleTimeout > 0)    $q->equal('idle-timeout', $idleTimeout . 's');
    $c->query($q)->read();
}

/**
 * Tambah atau update hotspot user di MikroTik.
 * Endpoint: /ip/hotspot/user
 */
public function upsertHotspotUser(Client $c, InternetCustomer $cust, string $profile): void
{
    $pwd  = trim((string) $cust->pass_hash) ?: 'admin123';
    $row  = $c->query((new Query('/ip/hotspot/user/print'))->where('comment', $cust->id))->read()[0] ?? null;

    if (!$row) {
        $q = (new Query('/ip/hotspot/user/add'))
            ->equal('name', $cust->username)
            ->equal('password', $pwd)
            ->equal('profile', $profile)
            ->equal('comment', $cust->id);
        $c->query($q)->read();
    } else {
        $q = (new Query('/ip/hotspot/user/set'))
            ->equal('.id', $row['.id'])
            ->equal('name', $cust->username)
            ->equal('password', $pwd)
            ->equal('profile', $profile);
        $c->query($q)->read();
    }
}

/**
 * Disable hotspot user (tidak hapus, hanya disabled).
 */
public function disableHotspotUser(Client $c, string $username): void
{
    if (!$username) return;
    $rows = $c->query((new Query('/ip/hotspot/user/print'))->where('name', $username))->read();
    if (empty($rows)) return;
    $c->query(
        (new Query('/ip/hotspot/user/set'))
            ->equal('.id', $rows[0]['.id'])
            ->equal('disabled', 'yes')
    )->read();
}

/**
 * Hapus hotspot user dari MikroTik.
 */
public function removeHotspotUser(Client $c, string $username): void
{
    if (!$username) return;
    $rows = $c->query((new Query('/ip/hotspot/user/print'))->where('name', $username))->read();
    foreach ($rows as $row) {
        $c->query((new Query('/ip/hotspot/user/remove'))->equal('.id', $row['.id']))->read();
    }
}

/**
 * Putus sesi hotspot yang sedang aktif.
 */
public function disconnectHotspotUser(Client $c, string $username): void
{
    if (!$username) return;
    $actives = $c->query((new Query('/ip/hotspot/active/print'))->where('user', $username))->read();
    foreach ($actives as $a) {
        $c->query((new Query('/ip/hotspot/active/remove'))->equal('.id', $a['.id']))->read();
    }
}

/**
 * Tambah IP Binding di hotspot MikroTik.
 * $type: 'regular' (login tetap, IP fixed) | 'bypassed' (tanpa login)
 * Binding bisa by IP, by MAC, atau keduanya.
 */
public function addHotspotIpBinding(
    Client $c,
    string $serverName,
    ?string $ip,
    ?string $mac,
    string $type = 'regular'
): void {
    if (!$ip && !$mac) return;

    // Cek existing binding (by MAC atau IP)
    $q = new Query('/ip/hotspot/ip-binding/print');
    if ($mac) $q->where('mac-address', $mac);
    elseif ($ip) $q->where('address', $ip);
    $existing = $c->query($q)->read()[0] ?? null;

    if ($existing) {
        $upd = (new Query('/ip/hotspot/ip-binding/set'))
            ->equal('.id', $existing['.id'])
            ->equal('type', $type);
        if ($serverName) $upd->equal('server', $serverName);
        if ($ip)  $upd->equal('address', $ip);
        if ($mac) $upd->equal('mac-address', $mac);
        $c->query($upd)->read();
    } else {
        $add = (new Query('/ip/hotspot/ip-binding/add'))
            ->equal('type', $type);
        if ($serverName) $add->equal('server', $serverName);
        if ($ip)  $add->equal('address', $ip);
        if ($mac) $add->equal('mac-address', $mac);
        $c->query($add)->read();
    }
}

/**
 * Hapus IP Binding hotspot.
 */
public function removeHotspotIpBinding(Client $c, ?string $ip, ?string $mac): void
{
    if (!$ip && !$mac) return;
    $q = new Query('/ip/hotspot/ip-binding/print');
    if ($mac) $q->where('mac-address', $mac);
    elseif ($ip) $q->where('address', $ip);
    $rows = $c->query($q)->read();
    foreach ($rows as $row) {
        $c->query((new Query('/ip/hotspot/ip-binding/remove'))->equal('.id', $row['.id']))->read();
    }
}

/**
 * Tambah/update voucher hotspot di MikroTik local.
 * Set time-limit dan limit-bytes-total dari profile voucher.
 */
public function upsertVoucherOnMikrotik(Client $c, HotspotVoucher $voucher): void
{
    $profile  = $voucher->internetPackage;
    $rate     = "{$profile->rate_down_mbps}M/{$profile->rate_up_mbps}M";
    $pwd      = $voucher->password;

    // Pastikan profile voucher ada
    $this->ensureHotspotUserProfile(
        $c,
        'VOC_' . $voucher->internet_package_id,
        $rate,
        $profile->session_timeout_seconds ?: null,
        null
    );

    $existing = $c->query((new Query('/ip/hotspot/user/print'))->where('name', $voucher->username))->read()[0] ?? null;

    if (!$existing) {
        $q = (new Query('/ip/hotspot/user/add'))
            ->equal('name', $voucher->username)
            ->equal('password', $pwd)
            ->equal('profile', 'VOC_' . $voucher->internet_package_id)
            ->equal('comment', $voucher->id);

        if ($profile->quota_bytes > 0) {
            $q->equal('limit-bytes-total', (string) $profile->quota_bytes);
        }
        $c->query($q)->read();
    } else {
        $q = (new Query('/ip/hotspot/user/set'))
            ->equal('.id', $existing['.id'])
            ->equal('password', $pwd)
            ->equal('profile', 'VOC_' . $voucher->internet_package_id);
        if ($profile->quota_bytes > 0) {
            $q->equal('limit-bytes-total', (string) $profile->quota_bytes);
        }
        $c->query($q)->read();
    }
}

// ============================================================

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

/**
 * Tambah/update Simple Queue untuk customer Static/IPoE.
 * Target = IP address customer, limit dari rate_down_mbps/rate_up_mbps paket.
 * Endpoint: /queue/simple
 */
public function upsertSimpleQueue(Client $c, InternetCustomer $cust, InternetPackage $pkg): void
{
    if (!$cust->ip_address) {
        return; // tidak ada IP, tidak ada yang bisa di-limit
    }

    $down = (int) ($pkg->rate_down_mbps ?? $pkg->bandwidth ?? 0);
    $up   = (int) ($pkg->rate_up_mbps ?? max(1, (int) ceil(($pkg->bandwidth ?? 1) * 0.2)));
    $maxLimit = "{$up}M/{$down}M"; // MikroTik format: upload/download

    $existing = $c->query(
        (new Query('/queue/simple/print'))->where('comment', $cust->id)
    )->read()[0] ?? null;

    if ($existing) {
        $q = (new Query('/queue/simple/set'))
            ->equal('.id', $existing['.id'])
            ->equal('target', $cust->ip_address . '/32')
            ->equal('max-limit', $maxLimit)
            ->equal('disabled', 'no');
        $c->query($q)->read();
    } else {
        $q = (new Query('/queue/simple/add'))
            ->equal('name', 'static-' . $cust->username)
            ->equal('target', $cust->ip_address . '/32')
            ->equal('max-limit', $maxLimit)
            ->equal('comment', $cust->id);
        $c->query($q)->read();
    }
}

/**
 * Disable (bukan hapus) simple queue customer — dipakai sebagai lapisan
 * kedua pertahanan saat suspend, di luar address-list ISOLIR.
 */
public function disableSimpleQueue(Client $c, string $customerId): void
{
    $rows = $c->query((new Query('/queue/simple/print'))->where('comment', $customerId))->read();
    foreach ($rows as $row) {
        $c->query(
            (new Query('/queue/simple/set'))
                ->equal('.id', $row['.id'])
                ->equal('disabled', 'yes')
        )->read();
    }
}

/**
 * Hapus simple queue customer (dipakai saat customer di-nonaktifkan permanen).
 */
public function removeSimpleQueue(Client $c, string $customerId): void
{
    $rows = $c->query((new Query('/queue/simple/print'))->where('comment', $customerId))->read();
    foreach ($rows as $row) {
        $c->query((new Query('/queue/simple/remove'))->equal('.id', $row['.id']))->read();
    }
}

/**
 * Tambah IP ke address-list ISOLIR_STATIC di MikroTik.
 *
 * PENTING: Ini CUMA menambahkan IP ke list-nya. Aksi blokir/redirect-nya
 * sendiri harus sudah dikonfigurasi SEKALI secara manual di MikroTik
 * (firewall filter/mangle/NAT yang mencocokkan address-list ini) — persis
 * seperti walled garden ISOLIR yang sudah ada untuk PPPoE. Lihat
 * STATIC_IPOE_SETUP.md untuk contoh rule firewall-nya.
 */
public function addToIsolirAddressList(Client $c, string $ip, ?string $comment = null): void
{
    if (!$ip) return;

    $existing = $c->query(
        (new Query('/ip/firewall/address-list/print'))
            ->where('list', 'ISOLIR_STATIC')
            ->where('address', $ip)
    )->read();

    if (!empty($existing)) {
        return; // sudah ada, tidak perlu duplikat
    }

    $q = (new Query('/ip/firewall/address-list/add'))
        ->equal('list', 'ISOLIR_STATIC')
        ->equal('address', $ip);
    if ($comment) $q->equal('comment', $comment);

    $c->query($q)->read();
}

/**
 * Hapus IP dari address-list ISOLIR_STATIC (dipanggil saat reactivate).
 */
public function removeFromIsolirAddressList(Client $c, string $ip): void
{
    if (!$ip) return;

    $rows = $c->query(
        (new Query('/ip/firewall/address-list/print'))
            ->where('list', 'ISOLIR_STATIC')
            ->where('address', $ip)
    )->read();

    foreach ($rows as $row) {
        $c->query((new Query('/ip/firewall/address-list/remove'))->equal('.id', $row['.id']))->read();
    }
}

// ============================================================
// CUSTOMER MONITORING PORTAL
// ============================================================

/**
 * Cek status PPPoE active untuk username tertentu.
 *
 * @param  Router $router  Router tempat customer terhubung.
 * @param  string $username Username PPPoE customer.
 * @return array{connected: bool, ip: ?string, uptime: ?string, caller_id: ?string}
 */
public function getPppoeStatus(Router $router, string $username): array
{
    $c = $this->client($router);

    $active = $c->query((new Query('/ppp/active/print'))->where('name', $username))->read()[0] ?? null;

    if (!$active) {
        return [
            'connected' => false,
            'ip'        => null,
            'uptime'    => null,
            'caller_id' => null,
        ];
    }

    return [
        'connected' => true,
        'ip'        => $active['address'] ?? null,
        'uptime'    => $active['uptime'] ?? null,
        'caller_id' => $active['caller-id'] ?? null,
    ];
}

/**
 * Ambil traffic realtime (rate) & total byte PPPoE customer dari interface
 * dinamis yang dibuat MikroTik saat sesi PPPoE aktif (`<pppoe-{username}>`).
 *
 * @param  Router $router  Router tempat customer terhubung.
 * @param  string $username Username PPPoE customer.
 * @return array{rx_rate: int, tx_rate: int, rx_bytes: int, tx_bytes: int}
 */
public function getPppoeTraffic(Router $router, string $username): array
{
    $c = $this->client($router);
    $ifaceName = "<pppoe-{$username}>";

    $result = ['rx_rate' => 0, 'tx_rate' => 0, 'rx_bytes' => 0, 'tx_bytes' => 0];

    $iface = $c->query((new Query('/interface/print'))->where('name', $ifaceName))->read()[0] ?? null;
    if ($iface) {
        $result['rx_bytes'] = (int) ($iface['rx-byte'] ?? 0);
        $result['tx_bytes'] = (int) ($iface['tx-byte'] ?? 0);
    }

    $monitor = $c->query(
        (new Query('/interface/monitor-traffic'))
            ->equal('interface', $ifaceName)
            ->equal('once', '')
    )->read()[0] ?? null;

    if ($monitor) {
        $result['rx_rate'] = (int) ($monitor['rx-bits-per-second'] ?? 0);
        $result['tx_rate'] = (int) ($monitor['tx-bits-per-second'] ?? 0);
    }

    return $result;
}

/**
 * Ping beberapa host dari router (mengukur latency dari sisi jaringan customer).
 *
 * @param  Router  $router Router yang akan menjalankan perintah ping.
 * @param  array<int, string> $hosts Daftar hostname/IP tujuan.
 * @return array<int, array{host: string, avg_rtt: ?float, loss: float}>
 */
public function pingHosts(Router $router, array $hosts): array
{
    $c = $this->client($router);
    $results = [];

    foreach ($hosts as $host) {
        try {
            $rows = $c->query(
                (new Query('/ping'))
                    ->equal('address', $host)
                    ->equal('count', 3)
            )->read();

            $times = [];
            foreach ($rows as $row) {
                if (isset($row['time'])) {
                    $times[] = (float) preg_replace('/[^0-9.]/', '', $row['time']);
                }
            }

            $sent     = count($rows);
            $received = count($times);
            $loss     = $sent > 0 ? round((($sent - $received) / $sent) * 100, 2) : 100.0;
            $avgRtt   = $received > 0 ? round(array_sum($times) / $received, 2) : null;

            $results[] = ['host' => $host, 'avg_rtt' => $avgRtt, 'loss' => $loss];
        } catch (\Throwable $e) {
            $results[] = ['host' => $host, 'avg_rtt' => null, 'loss' => 100.0];
        }
    }

    return $results;
}

/**
 * Putuskan sesi PPPoE customer yang sedang aktif (dipakai untuk fitur "Restart PPPoE").
 *
 * @param  Router $router  Router tempat customer terhubung.
 * @param  string $username Username PPPoE customer.
 * @return bool True jika berhasil diproses (termasuk saat tidak ada sesi aktif).
 */
public function disconnectPppoe(Router $router, string $username): bool
{
    if (!$username) {
        return false;
    }

    $c = $this->client($router);
    $actives = $c->query((new Query('/ppp/active/print'))->where('name', $username))->read();

    foreach ($actives as $active) {
        $c->query((new Query('/ppp/active/remove'))->equal('.id', $active['.id']))->read();
    }

    return true;
}

/**
 * Ping ke router itu sendiri untuk mengukur latency API MikroTik.
 *
 * @param  Router $router Router yang akan di-ping.
 * @return array{latency_ms: ?float, status: 'online'|'offline'}
 */
public function pingRouter(Router $router): array
{
    $start = microtime(true);

    try {
        $c = $this->client($router);
        $c->query(new Query('/system/identity/print'))->read();

        return [
            'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            'status'     => 'online',
        ];
    } catch (\Throwable $e) {
        return [
            'latency_ms' => null,
            'status'     => 'offline',
        ];
    }
}
}