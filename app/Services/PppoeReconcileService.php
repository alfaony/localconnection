<?php

namespace App\Services;

use App\Models\InternetCustomer;
use App\Models\PackageRouterProfile;
use App\Models\Router;
use RouterOS\Query;

/**
 * Reconcile PPP secret yang sudah ada manual di MikroTik (biasanya dari
 * sebelum ISP pakai sistem ini) dengan data InternetCustomer.
 */
class PppoeReconcileService
{
    public function __construct(protected RouterOSService $ros) {}

    /**
     * Scan /ppp/secret/print di router, cocokkan dengan InternetCustomer
     * yang sudah terdaftar (by username), dan sarankan paket berdasarkan
     * mapping PackageRouterProfile (profile PPP -> InternetPackage).
     *
     * @return array<int, array{
     *   username: string, profile: string, service: string, disabled: bool,
     *   comment: ?string, matched_customer: ?InternetCustomer,
     *   suggested_package: ?\App\Models\InternetPackage
     * }>
     */
    public function discover(Router $router): array
    {
        $client = $this->ros->client($router);

        // Preflight: sama seperti StaticReconcileService, gagal cepat
        // (10 detik) kalau router unreachable, jangan nunggu sampai
        // max_execution_time global baru fatal error.
        set_time_limit(10);
        if (!$this->ros->quickPing($client)) {
            throw new \RuntimeException(
                "Router '{$router->name}' tidak merespon. Kemungkinan: router mati/unreachable, " .
                "firewall MikroTik/server memblokir port API, atau service API di MikroTik nonaktif. " .
                "Cek dulu koneksi manual (telnet {$router->host} {$router->port}) sebelum coba lagi."
            );
        }

        set_time_limit(60);

        // .proplist: sama seperti StaticReconcileService, cuma minta
        // field yang dipakai (name, profile, service, disabled, comment)
        // biar transfer + parsing lebih cepat kalau jumlah secret-nya banyak.
        $secrets = $client->query(
            (new Query('/ppp/secret/print'))->equal('.proplist', 'name,profile,service,disabled,comment')
        )->read();

        $existingCustomers = InternetCustomer::where('router_id', $router->id)
            ->where('access_type', 'pppoe')
            ->whereNotNull('username')
            ->get(['id', 'name', 'code', 'username', 'status'])
            ->keyBy('username');

        $profileMap = PackageRouterProfile::where('router_id', $router->id)
            ->with('package')
            ->get()
            ->keyBy('ros_profile');

        $result = [];

        foreach ($secrets as $row) {
            $username = $row['name'] ?? null;
            if (!$username) {
                continue;
            }

            $profile = $row['profile'] ?? null;
            $matched = $existingCustomers->get($username);
            $suggestedPackage = $profile ? $profileMap->get($profile)?->package : null;

            $result[] = [
                'username' => $username,
                'profile' => $profile,
                'service' => $row['service'] ?? 'pppoe',
                'disabled' => ($row['disabled'] ?? 'false') === 'true',
                'comment' => $row['comment'] ?? null,
                'matched_customer' => $matched,
                'suggested_package' => $suggestedPackage,
            ];
        }

        return $result;
    }
}
