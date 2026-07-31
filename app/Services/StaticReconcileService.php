<?php


namespace App\Services;

use App\Models\InternetCustomer;
use App\Models\Router;
use RouterOS\Query;

/**
 * Reconcile perangkat yang terdeteksi di ARP table MikroTik dengan data
 * InternetCustomer di sistem. Dipakai khusus untuk customer Static/IPoE
 * (yang tidak punya PPP secret / hotspot user sebagai "identitas").
 */
class StaticReconcileService
{
    public function __construct(protected RouterOSService $ros) {}

    /**
     * Scan ARP table di router, cocokkan dengan InternetCustomer yang sudah
     * terdaftar di router yang sama (by ip_address atau mac_address).
     *
     * @return array<int, array{ip: string, mac: ?string, interface: ?string, matched_customer: ?InternetCustomer}>
     */
    public function discover(Router $router): array
    {
        $client = $this->ros->client($router);
        $arpRows = $client->query(new Query('/ip/arp/print'))->read();

        // Ambil semua customer static yang sudah terdaftar di router ini,
        // supaya matching di bawah tidak query per baris (N+1).
        $existingCustomers = InternetCustomer::where('router_id', $router->id)
            ->where('access_type', 'ipoe')
            ->get(['id', 'name', 'code', 'ip_address', 'mac_address', 'status']);

        $byIp = $existingCustomers->keyBy('ip_address');
        $byMac = $existingCustomers->keyBy(fn ($c) => $c->mac_address ? strtoupper($c->mac_address) : null);

        $result = [];

        foreach ($arpRows as $row) {
            $ip = $row['address'] ?? null;
            $mac = $row['mac-address'] ?? null;

            if (!$ip) {
                continue; // baris tanpa IP tidak berguna buat kita
            }

            // Skip ARP entry milik router itu sendiri (biasanya flag "DHCP"=false
            // dan interface = bridge/gateway-nya sendiri) — heuristik sederhana:
            // kalau ip-nya sama dengan salah satu gateway AddressPool router ini, skip.
            if (($row['interface'] ?? null) && str_contains(strtolower($row['interface']), 'loopback')) {
                continue;
            }

            $matched = $byIp->get($ip) ?? ($mac ? $byMac->get(strtoupper($mac)) : null);

            $result[] = [
                'ip' => $ip,
                'mac' => $mac,
                'interface' => $row['interface'] ?? null,
                'matched_customer' => $matched,
            ];
        }

        return $result;
    }
}
