<?php

namespace App\Jobs;

use App\Models\{Router, RouterInterface, AddressPool, InternetPackage, PackageRouterProfile, InternetCustomer, PppoeServer};
use App\Services\RouterOSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use RouterOS\Query;
use Throwable;

class SyncRouterInventoryJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    public int $tries = 3;
    public int $timeout = 120; // detik

    public function __construct(
        public int $routerId,
        public bool $withProfiles = false,
        public bool $withSecrets  = false,
        public bool $withSessions = false,
        public bool $withPppoe    = false,
        public bool $ensureProfiles = false,   // ⬅️ baru: create/update profile di router
    ) {}

    public function middleware(): array
    {
        // kunci per-router agar tidak overlap & rate limit API ke router tsb
        return [
            new WithoutOverlapping("sync-router-{$this->routerId}"),
            (new RateLimited("mikrotik-{$this->routerId}"))->dontRelease(), // gunakan rate limiter default
        ];
    }

    public function handle(RouterOSService $svc): void
    {
        $router = Router::findOrFail($this->routerId);
        $c = $svc->client($router);

        // 1) Interfaces
        $ifs = $c->query(new Query('/interface/print'))->read();
        foreach ($ifs as $row) {
            $name = $row['name'] ?? null; if (!$name) continue;
            RouterInterface::updateOrCreate(
                ['router_id' => $router->id, 'name' => $name],
                ['role' => ($row['type'] ?? '') === 'bridge' ? 'management' : 'access', 'meta' => $row]
            );
        }
        // VLAN
        $vlans = $c->query(new Query('/interface/vlan/print'))->read();
        foreach ($vlans as $v) {
            $name = $v['name'] ?? null; if (!$name) continue;
            RouterInterface::updateOrCreate(
                ['router_id'=>$router->id, 'name'=>$name],
                ['role'=>'access', 'vlan_id'=>(int)($v['vlan-id'] ?? 0), 'meta'=>$v]
            );
        }

        // 2) Address Pools
        $pools = $c->query(new Query('/ip/pool/print'))->read();
        foreach ($pools as $p) {
            $name = $p['name'] ?? null; if (!$name) continue;
            $ranges = $p['ranges'] ?? null;
            $cidr = $this->cidrFromRangesIfObvious($ranges) ?? ($ranges ?? 'unknown');
            AddressPool::updateOrCreate(
                ['name' => $name, 'cidr' => $cidr],
                ['meta' => $p]
            );
        }

        // 3) PPPoE servers (opsional)
        if ($this->withPppoe) {
            $servers = $c->query(new Query('/interface/pppoe-server/server/print'))->read();
            $seenNames = [];
            foreach ($servers as $srv) {
                $name = $srv['service-name'] ?? null; if (!$name) continue;
                $seenNames[] = $name;

                $ifaceName = $srv['interface'] ?? null;
                $iface = $ifaceName
                    ? RouterInterface::where('router_id',$router->id)->where('name',$ifaceName)->first()
                    : null;

                $poolName = $srv['remote-address'] ?? null;
                $pool = $poolName
                    ? AddressPool::where('name',$poolName)->first()
                    : null;

                PppoeServer::updateOrCreate(
                    ['router_id'=>$router->id, 'service_name'=>$name],
                    [
                        'interface_id'    => $iface?->id,
                        'address_pool_id' => $pool?->id,
                        'only_one'        => (($srv['one-session-per-host'] ?? $srv['only-one'] ?? 'yes') === 'yes'),
                        'meta'            => $srv,
                    ]
                );
            }
            // optional: hapus yang sudah tidak ada di router
            if (!empty($seenNames)) {
                PppoeServer::where('router_id',$router->id)
                    ->whereNotIn('service_name',$seenNames)
                    ->delete();
            }
        }

        // 4) Profiles → auto-map paket (opsional)
        if ($this->withProfiles) 
        {
            $profiles = $c->query(new Query('/ppp/profile/print'))->read();
            $names    = collect($profiles)->pluck('name')->filter()->values()->all();

            foreach (InternetPackage::cursor() as $pkg) {
                $down = (int) ($pkg->rate_down_mbps ?? $pkg->bandwidth ?? 0);
                $up   = (int) ($pkg->rate_up_mbps   ?? max(1, (int)ceil(($pkg->bandwidth ?? 1) * 0.2)));
                $rate = "{$down}M/{$up}M";

                // nama target (boleh pakai mapping jika sudah ada; kalau belum tebak)
                $map   = \App\Models\PackageRouterProfile::where('router_id',$router->id)->where('package_id',$pkg->id)->first();
                $guess = $map?->ros_profile ?: ($pkg->name ?: ("PLAN_{$down}M"));
                $targetProfile = $this->findNameNormalized($names, $this->normalize($guess)) ?: $guess;

                // 1) Pastikan mapping DB ada
                \App\Models\PackageRouterProfile::updateOrCreate(
                    ['router_id'=>$router->id, 'package_id'=>$pkg->id],
                    ['ros_profile'=>$targetProfile]
                );

                // 2) (opsional) Pastikan profile ada di MikroTik
                if ($this->ensureProfiles) {
                    $existing = $c->query((new Query('/ppp/profile/print'))->where('name',$targetProfile))->read();
                    if (empty($existing)) {
                        // create
                        $c->query(
                            (new Query('/ppp/profile/add'))
                                ->equal('name', $targetProfile)
                                ->equal('only-one','yes')
                                ->equal('rate-limit', $rate)
                        )->read();
                    } else {
                        // update rate-limit supaya sesuai DB
                        $c->query(
                            (new Query('/ppp/profile/set'))
                                ->equal('.id', $existing[0]['.id'])
                                ->equal('rate-limit', $rate)
                                ->equal('only-one','yes')
                        )->read();
                    }

                    // (opsional) FUP profile
                    if (($pkg->quota_bytes ?? 0) > 0) {
                        $fupDown = (int) $pkg->fup_rate_down_mbps;
                        $fupUp   = (int) $pkg->fup_rate_up_mbps;
                        $fupRate = "{$fupDown}M/{$fupUp}M";
                        $fupName = "{$targetProfile}_FUP";

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
                                    ->equal('rate-limit',$fupRate)
                                    ->equal('only-one','yes')
                            )->read();
                        }
                    }
                }
            }
        }

        // 5) Secrets → reconcile meta (opsional)
        if ($this->withSecrets) {
            $secrets = $c->query(new Query('/ppp/secret/print'))->read();
            $byName  = collect($secrets)->keyBy(fn($s)=>$s['name'] ?? null);
            $custs = InternetCustomer::where('router_id',$router->id)
                     ->where('access_type','pppoe')->whereNotNull('username')->cursor();
            foreach ($custs as $cust) {
                $s = $byName->get($cust->username); if (!$s) continue;
                $meta = (array) $cust->meta;
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

        // 6) Sessions → update ip/mac (opsional)
        if ($this->withSessions) {
            $actives = $c->query(new Query('/ppp/active/print'))->read();
            $byName  = collect($actives)->keyBy(fn($a)=>$a['name'] ?? null);
            $custs = InternetCustomer::where('router_id',$router->id)
                     ->where('access_type','pppoe')->whereNotNull('username')->cursor();
            foreach ($custs as $cust) {
                $a = $byName->get($cust->username); if (!$a) continue;
                $cust->update([
                    'ip_address'  => $a['address']   ?? $cust->ip_address,
                    'mac_address' => $a['caller-id'] ?? $cust->mac_address,
                ]);
            }
        }
    }

    public function failed(Throwable $e): void
    {
        // kamu bisa tulis ke audit/logs khusus router
        \Log::error("SyncRouterInventoryJob failed: {$e->getMessage()}", ['router_id'=>$this->routerId]);
    }

    // helpers
    private function normalize(string $s): string
    {
        return preg_replace('/[^A-Z0-9]/','',strtoupper($s));
    }
    private function findNameNormalized(array $names, string $needle): ?string
    {
        foreach ($names as $n) if ($this->normalize($n) === $needle) return $n;
        return null;
    }
    private function cidrFromRangesIfObvious(?string $ranges): ?string
    {
        if (!$ranges || !str_contains($ranges,'-')) return null;
        [$a,$b] = explode('-',$ranges,2);
        $pa = explode('.',trim($a)); $pb = explode('.',trim($b));
        if (count($pa)===4 && count($pb)===4 && $pa[0]===$pb[0] && $pa[1]===$pb[1] && $pa[2]===$pb[2]) {
            return "{$pa[0]}.{$pa[1]}.{$pa[2]}.0/24";
        }
        return null;
    }
}