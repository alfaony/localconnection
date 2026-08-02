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

        // Preflight: cek konektivitas dulu dengan query super ringan
        // (/system/identity/print), time limit dipersempit jadi 10 detik
        // biar gagal CEPAT kalau router unreachable/timeout, bukan nunggu
        // sampai max_execution_time global (biasanya 30 detik) baru mati
        // dengan fatal error yang jelek.
        set_time_limit(10);
        if (!$this->ros->quickPing($client)) {
            throw new \RuntimeException(
                "Router '{$router->name}' tidak merespon. Kemungkinan: router mati/unreachable, " .
                "firewall MikroTik/server memblokir port API, atau service API di MikroTik nonaktif. " .
                "Cek dulu koneksi manual (telnet {$router->host} {$router->port}) sebelum coba lagi."
            );
        }

        // Ping berhasil, kasih waktu lebih longgar buat walk data yang
        // bisa lebih berat (ratusan/ribuan ARP entry atau PPP secret)
        set_time_limit(60);

        // .proplist: cuma minta field yang beneran dipakai, bukan semua
        // field default MikroTik (yang bisa jauh lebih banyak per row).
        // Ini ngurangin ukuran data yang ditransfer + waktu proses di
        // router-nya sendiri, penting kalau ARP table-nya besar.
        $arpRows = $client->query(
            (new Query('/ip/arp/print'))->equal('.proplist', 'address,mac-address,interface')
        )->read();

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
