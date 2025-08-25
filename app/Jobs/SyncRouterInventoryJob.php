<?php

namespace App\Jobs;

use App\Models\{
    Router, RouterInterface, AddressPool, InternetPackage,
    PackageRouterProfile, InternetCustomer, PppoeServer
};
use App\Services\RouterOSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use RouterOS\Query;
use Illuminate\Support\Str;
use Throwable;

class SyncRouterInventoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Batas waktu eksekusi tiap attempt (detik) – jangan terlalu besar agar fail fast */
    public int $timeout = 90;

    /** Maksimal percobaan */
    public int $tries = 5;

    /** Backoff antar retry (detik) */
    public function backoff(): array { return [10, 30, 90, 180]; }

    public function __construct(
        public int  $routerId,
        public bool $withProfiles     = false,
        public bool $withSecrets      = false,
        public bool $withSessions     = false,
        public bool $withPppoe        = false,
        public bool $ensureProfiles   = false  // create/update profile di router sesuai paket
    ) {}

    // public function middleware(): array
    // {
    //     return [
    //         // Lock per-router agar job tidak tumpang tindih
    //         (new WithoutOverlapping("sync-router-{$this->routerId}"))->expireAfter(300),

    //         // Rate limit per-router (opsional; pastikan rate limiter "mikrotik-{id}" ada/terpakai)
    //         (new RateLimited("mikrotik-{$this->routerId}"))->dontRelease(),
    //     ];
    // }

    public function handle(RouterOSService $svc): void
    {
        $router = Router::findOrFail($this->routerId);

        // Router non-aktif → keluar cepat
        // if (property_exists($router, 'active') && !$router->active) {
        //     \Log::info('[SyncRouter] router inactive, skip', ['router_id' => $router->id]);
        //     return;
        // }
        
        if($router->active != "UP") {
            \Log::info('[SyncRouter] router inactive, skip', ['router_id' => $router->id]);
            return;
        }

        // Koneksi client + quick ping untuk fail-fast
        $c = $svc->client($router);
        if (method_exists($svc, 'quickPing') && !$svc->quickPing($c)) {
            \Log::warning('[SyncRouter] quickPing failed, release', ['router_id' => $router->id]);
            $this->release(60);
            return;
        }

        // ---- 1) Interfaces & VLANs
        try {
            $this->syncInterfaces($c, $router);
            $this->syncVlans($c, $router);
        } catch (Throwable $e) {
            \Log::warning('[SyncRouter] interfaces/vlans fail', ['router_id'=>$router->id, 'error'=>$e->getMessage()]);
        }

        // ---- 2) Address Pools
        try {
            $this->syncAddressPools($c, $router);
        } catch (Throwable $e) {
            \Log::warning('[SyncRouter] pools fail', ['router_id'=>$router->id, 'error'=>$e->getMessage()]);
        }

        // ---- 3) PPPoE servers (opsional)
        if ($this->withPppoe) {
            try {
                $this->syncPppoeServers($c, $router);
            } catch (Throwable $e) {
                \Log::warning('[SyncRouter] pppoe servers fail', ['router_id'=>$router->id, 'error'=>$e->getMessage()]);
            }
        }

        // Ambil sekali daftar profiles (hemat query) bila diperlukan
        $profilesCache = null;
        if ($this->withProfiles || $this->ensureProfiles) {
            try {
                $profilesCache = $c->query(new Query('/ppp/profile/print'))->read();
            } catch (Throwable $e) {
                \Log::warning('[SyncRouter] read profiles fail', ['router_id'=>$router->id, 'error'=>$e->getMessage()]);
            }
        }

        // ---- 4) Profiles → auto-map; ensure (opsional)
        if ($this->withProfiles || $this->ensureProfiles) {
            try {
                $this->scanOrEnsureProfiles($c, $router, $profilesCache);
            } catch (Throwable $e) {
                \Log::warning('[SyncRouter] profiles scan/ensure fail', ['router_id'=>$router->id, 'error'=>$e->getMessage()]);
            }
        }

        // ---- 5) Secrets → reconcile meta (opsional)
        if ($this->withSecrets) {
            try {
                $this->reconcileSecrets($c, $router);
            } catch (Throwable $e) {
                \Log::warning('[SyncRouter] secrets reconcile fail', ['router_id'=>$router->id, 'error'=>$e->getMessage()]);
            }
        }

        // ---- 6) Sessions → update ip/mac (opsional)
        if ($this->withSessions) {
            try {
                $this->syncSessions($c, $router);
            } catch (Throwable $e) {
                \Log::warning('[SyncRouter] sessions sync fail', ['router_id'=>$router->id, 'error'=>$e->getMessage()]);
            }
        }
    }

    public function failed(Throwable $e): void
    {
        \Log::error('[SyncRouter] job failed', [
            'router_id' => $this->routerId,
            'message'   => $e->getMessage(),
        ]);
    }

    /** ================== SEGMENTS ================== */

    private function syncInterfaces($c, Router $router): void
    {
        $rows = $c->query(new Query('/interface/print'))->read();
        foreach ($rows as $row) {
            $name = $row['name'] ?? null;
            if (!$name) continue;

            RouterInterface::updateOrCreate(
                ['router_id' => $router->id, 'name' => $name],
                [
                    'role' => (strtolower($row['type'] ?? '') === 'bridge') ? 'management' : 'access',
                    'meta' => $row,
                ]
            );
        }
    }

    private function syncVlans($c, Router $router): void
    {
        $rows = $c->query(new Query('/interface/vlan/print'))->read();
        foreach ($rows as $row) {
            $name = $row['name'] ?? null;
            if (!$name) continue;

            RouterInterface::updateOrCreate(
                ['router_id' => $router->id, 'name' => $name],
                [
                    'role'    => 'access',
                    'vlan_id' => (int)($row['vlan-id'] ?? 0),
                    'meta'    => $row,
                ]
            );
        }
    }

    private function syncAddressPools($c, Router $router): void
    {
        $rows = $c->query(new Query('/ip/pool/print'))->read();
        foreach ($rows as $row) {
            $name   = $row['name']   ?? null;
            $ranges = $row['ranges'] ?? null;
            if (!$name) continue;

            $cidr = $this->cidrFromRangesIfObvious($ranges) ?? ($ranges ?? 'unknown');

            // skema kamu unique(['name','cidr']) → kunci dengan dua kolom
            AddressPool::updateOrCreate(
                ['name' => $name, 'cidr' => $cidr],
                ['meta' => $row]
            );
        }
    }

    private function syncPppoeServers($c, Router $router): void
    {
        $rows = $c->query(new Query('/interface/pppoe-server/server/print'))->read();

        $seen = [];
        foreach ($rows as $srv) {
            $svcName = $srv['service-name'] ?? null;
            if (!$svcName) continue;
            $seen[] = $svcName;

            $iface = null;
            if (!empty($srv['interface'])) {
                $iface = RouterInterface::where('router_id', $router->id)
                    ->where('name', $srv['interface'])
                    ->first();
            }

            $pool = null;
            if (!empty($srv['remote-address'])) {
                $pool = AddressPool::where('name', $srv['remote-address'])->first();
            }

            PppoeServer::updateOrCreate(
                ['router_id' => $router->id, 'service_name' => $svcName],
                [
                    'interface_id'    => $iface?->id,
                    'address_pool_id' => $pool?->id,
                    'only_one'        => (($srv['one-session-per-host'] ?? $srv['only-one'] ?? 'yes') === 'yes'),
                    'meta'            => $srv,
                ]
            );
        }

        // bersihkan yang tidak ada lagi di router
        if ($seen) {
            PppoeServer::where('router_id', $router->id)
                ->whereNotIn('service_name', $seen)
                ->delete();
        }
    }

    private function scanOrEnsureProfiles($c, Router $router, ?array $profilesCache): void
    {
        $names = [];
        if (is_array($profilesCache)) {
            $names = collect($profilesCache)->pluck('name')->filter()->values()->all();
        } else {
            // fallback kalau cache gagal
            $profiles = $c->query(new Query('/ppp/profile/print'))->read();
            $names = collect($profiles)->pluck('name')->filter()->values()->all();
        }

        foreach (InternetPackage::cursor() as $pkg) {
            $down = (int)($pkg->rate_down_mbps ?? $pkg->bandwidth ?? 0);
            $up   = (int)($pkg->rate_up_mbps   ?? max(1, (int)ceil(($pkg->bandwidth ?? 1) * 0.2)));
            $rate = "{$down}M/{$up}M";

            $map   = PackageRouterProfile::where('router_id', $router->id)
                        ->where('package_id', $pkg->id)->first();
            $guess = $map?->ros_profile ?: ($pkg->name ?: "PLAN_{$down}M");

            $targetProfile = $this->findNameNormalized($names, $this->normalize($guess)) ?: $guess;

            // pastikan mapping ada
            PackageRouterProfile::updateOrCreate(
                ['router_id' => $router->id, 'package_id' => $pkg->id],
                ['ros_profile' => $targetProfile]
            );

            if (!$this->ensureProfiles) continue;


            $this->ensureMissingSecrets($c, $router);
            // ensure profile normal
            // $existing = $c->query((new Query('/ppp/profile/print'))->where('name', $targetProfile))->read();
            // if (empty($existing)) {
            //     $c->query(
            //         (new Query('/ppp/profile/add'))
            //             ->equal('name', $targetProfile)
            //             ->equal('only-one', 'yes')
            //             ->equal('rate-limit', $rate)
            //     )->read();
            // } else {
            //     $c->query(
            //         (new Query('/ppp/profile/set'))
            //             ->equal('.id', $existing[0]['.id'])
            //             ->equal('rate-limit', $rate)
            //             ->equal('only-one', 'yes')
            //     )->read();
            // }

            // // ensure FUP profile jika paket berkuota
            // if (($pkg->quota_bytes ?? 0) > 0) {
            //     $fupRate = "{$pkg->fup_rate_down_mbps}M/{$pkg->fup_rate_up_mbps}M";
            //     $fupName = "{$targetProfile}_FUP";

            //     $fExist = $c->query((new Query('/ppp/profile/print'))->where('name', $fupName))->read();
            //     if (empty($fExist)) {
            //         $c->query(
            //             (new Query('/ppp/profile/add'))
            //                 ->equal('name', $fupName)
            //                 ->equal('only-one', 'yes')
            //                 ->equal('rate-limit', $fupRate)
            //         )->read();
            //     } else {
            //         $c->query(
            //             (new Query('/ppp/profile/set'))
            //                 ->equal('.id', $fExist[0]['.id'])
            //                 ->equal('rate-limit', $fupRate)
            //                 ->equal('only-one', 'yes')
            //         )->read();
            //     }
            // }
        }
    }


    private function ensureMissingSecrets($c, Router $router): void
    {
        // Cache: daftar secret yg sudah ada di router
        $secrets = $c->query(new Query('/ppp/secret/print'))->read();
        $have    = collect($secrets)->pluck('name')->filter()->flip(); // map[name=>true]

        $custs = InternetCustomer::where('router_id', $router->id)
            ->where('access_type','pppoe')
            ->where('status','active')
            ->whereNotNull('username')
            ->with('internetPackage')
            ->cursor();

        foreach ($custs as $cust) {
            if ($have->has($cust->username)) {
                continue; // secret sudah ada di router
            }

            // Tentukan profile dari mapping (fallback)
            $pkg = $cust->internetPackage;
            if (!$pkg) { continue; }

            $map = PackageRouterProfile::where('router_id',$router->id)
                    ->where('package_id',$pkg->id)->first();

            $profile = $map->ros_profile ?: ('PKG_'.$pkg->id);

            // ✅ pastikan profile ada (buat/update kalau perlu)
            $this->ensureProfileExists($c, $pkg, $profile);
            
            // ROTATE password baru utk secret yg dibuat ulang
            $plain = $cust->pass_hash ?? Str::random(10);

            // Buat secret
            $resp = $c->query(
                (new Query('/ppp/secret/add'))
                    ->equal('name', $cust->username)
                    ->equal('password', $plain)
                    ->equal('service','pppoe')
                    ->equal('profile', $profile)
                    ->equal('disabled','no')
            )->read();

            // (opsional) simpan plain terenkripsi utk notifikasi
            // $cust->pass_hash = Crypt::encryptString($plain);
            // $cust->save();

            // (opsional) catat ke log/audit
            \Log::info('[EnsureSecret] created', [
                'router_id' => $router->id,
                'username'  => $cust->username,
                'profile'   => $profile,
                'resp'      => $resp[0]['ret'] ?? null,
            ]);
        }
    }

    /**
     * Pastikan PPP profile ada di router; buat atau update rate-limit sesuai paket.
     * Sekalian bikin FUP profile jika paket pakai kuota.
     */
    private function ensureProfileExists($c, InternetPackage $pkg, string $profileName): void
    {
        // Hitung rate normal (fallback bila field null)
        $down = (int) ($pkg->rate_down_mbps ?? $pkg->bandwidth ?? 0);
        $up   = (int) ($pkg->rate_up_mbps   ?? max(1, (int)ceil(($pkg->bandwidth ?? 1) * 0.2)));
        $rate = "{$down}M/{$up}M";

        // cek profile normal
        $exist = $c->query((new Query('/ppp/profile/print'))->where('name',$profileName))->read();
        if (empty($exist)) {
            // create
            $c->query(
                (new Query('/ppp/profile/add'))
                    ->equal('name',$profileName)
                    ->equal('only-one','yes')
                    ->equal('rate-limit',$rate)
            )->read();
        } else {
            // update rate-limit kalau perlu
            $c->query(
                (new Query('/ppp/profile/set'))
                    ->equal('.id',$exist[0]['.id'])
                    ->equal('only-one','yes')
                    ->equal('rate-limit',$rate)
            )->read();
        }

        // Jika paket berkuota → ensure FUP profile
        if (($pkg->quota_bytes ?? 0) > 0) {
            $fupName = "{$profileName}_FUP";
            $fupRate = (int)$pkg->fup_rate_down_mbps . 'M/' . (int)$pkg->fup_rate_up_mbps . 'M';

            $fExist = $c->query((new Query('/ppp/profile/print'))->where('name',$fupName))->read();
            if (empty($fExist)) {
                $c->query(
                    (new Query('/ppp/profile/add'))
                        ->equal('name',$fupName)
                        ->equal('only-one','yes')
                        ->equal('rate-limit',$fupRate)
                )->read();
            } else {
                $c->query(
                    (new Query('/ppp/profile/set'))
                        ->equal('.id',$fExist[0]['.id'])
                        ->equal('only-one','yes')
                        ->equal('rate-limit',$fupRate)
                )->read();
            }
        }
    }

    private function reconcileSecrets($c, Router $router): void
    {
        $secrets = $c->query(new Query('/ppp/secret/print'))->read();
        $byName  = collect($secrets)->keyBy(fn($s) => $s['name'] ?? null);

        $custs = InternetCustomer::where('router_id', $router->id)
            ->where('access_type', 'pppoe')
            ->whereNotNull('username')
            ->cursor();

        foreach ($custs as $cust) {
            $s = $byName->get($cust->username);
            if (!$s) continue;

            $meta = (array)$cust->meta;
            $meta['ros_secret'] = [
                'id'       => $s['.id'] ?? null,
                'disabled' => $s['disabled'] ?? null,
                'profile'  => $s['profile'] ?? null,
                'comment'  => $s['comment'] ?? null,
            ];
            $cust->meta = $meta;
            $cust->save();
        }
    }

    private function syncSessions($c, Router $router): void
    {
        $actives = $c->query(new Query('/ppp/active/print'))->read(); // name, address, caller-id
        $byName  = collect($actives)->keyBy(fn($a) => $a['name'] ?? null);

        $custs = InternetCustomer::where('router_id', $router->id)
            ->where('access_type', 'pppoe')
            ->whereNotNull('username')
            ->cursor();

        foreach ($custs as $cust) {
            $a = $byName->get($cust->username);
            if (!$a) continue;

            $cust->update([
                'ip_address'  => $a['address']   ?? $cust->ip_address,
                'mac_address' => $a['caller-id'] ?? $cust->mac_address,
            ]);
        }
    }

    /** ================== HELPERS ================== */

    private function normalize(string $s): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper($s));
    }

    private function findNameNormalized(array $names, string $needleNorm): ?string
    {
        foreach ($names as $n) {
            if ($this->normalize((string)$n) === $needleNorm) return (string)$n;
        }
        return null;
    }

    private function cidrFromRangesIfObvious(?string $ranges): ?string
    {
        if (!$ranges || !str_contains($ranges, '-')) return null;
        [$a, $b] = explode('-', $ranges, 2);
        $pa = explode('.', trim($a));
        $pb = explode('.', trim($b));
        if (count($pa) === 4 && count($pb) === 4 && $pa[0] === $pb[0] && $pa[1] === $pb[1] && $pa[2] === $pb[2]) {
            return "{$pa[0]}.{$pa[1]}.{$pa[2]}.0/24";
        }
        return null;
    }
}