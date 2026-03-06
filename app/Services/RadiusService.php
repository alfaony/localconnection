<?php

namespace App\Services;

use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\Radius\RadCheck;
use App\Models\Radius\RadReply;
use App\Models\Radius\RadGroupReply;
use App\Models\Radius\RadUserGroup;
use App\Models\Radius\RadAcct;
use Illuminate\Support\Facades\Log;

class RadiusService
{
    /**
     * Pastikan group/profile ada di RADIUS dengan rate-limit yang benar.
     * Menggantikan: RouterOSService::ensurePppProfile()
     *
     * @param InternetPackage $pkg
     * @param string $groupName  Nama group (ex: PKG_10M, HikariLite)
     */
    public function ensureGroup(InternetPackage $pkg, string $groupName): void
    {
        $down = (int)($pkg->rate_down_mbps ?? $pkg->bandwidth ?? 0);
        $up   = (int)($pkg->rate_up_mbps ?? max(1, (int)ceil(($pkg->bandwidth ?? 1) * 0.2)));
        $rate = "{$down}M/{$up}M";

        // Upsert Mikrotik-Rate-Limit di radgroupreply
        RadGroupReply::updateOrCreate(
            ['groupname' => $groupName, 'attribute' => 'Mikrotik-Rate-Limit'],
            ['op' => ':=', 'value' => $rate]
        );

        Log::info('[RadiusService] ensureGroup', [
            'group' => $groupName,
            'rate'  => $rate,
        ]);
    }

    /**
     * Buat/update user di RADIUS (password + group mapping).
     * Menggantikan: RouterOSService::upsertPppSecret()
     *
     * @param InternetCustomer $cust
     * @param string $groupName
     */
    public function upsertUser(InternetCustomer $cust, string $groupName): void
    {
        $password = trim((string) $cust->pass_hash) ?: 'admin123';

        // Set password di radcheck
        RadCheck::updateOrCreate(
            ['username' => $cust->username, 'attribute' => 'Cleartext-Password'],
            ['op' => ':=', 'value' => $password]
        );

        // Map user ke group/paket
        RadUserGroup::updateOrCreate(
            ['username' => $cust->username],
            ['groupname' => $groupName, 'priority' => 1]
        );

        // Static IP per user (opsional)
        if ($cust->local_address) {
            RadReply::updateOrCreate(
                ['username' => $cust->username, 'attribute' => 'Framed-IP-Address'],
                ['op' => ':=', 'value' => $cust->local_address]
            );
        } else {
            // Hapus static IP kalau tidak diset
            RadReply::where('username', $cust->username)
                ->where('attribute', 'Framed-IP-Address')
                ->delete();
        }

        Log::info('[RadiusService] upsertUser', [
            'username' => $cust->username,
            'group'    => $groupName,
        ]);
    }

    /**
     * Suspend user — block authentication dan disconnect session.
     * Menggantikan: RouterOSService::disableSecret() + set profile SUSPENDED
     *
     * @param string $username
     */
    public function suspendUser(string $username): void
    {
        // Set Auth-Type Reject → RADIUS akan tolak login
        RadCheck::updateOrCreate(
            ['username' => $username, 'attribute' => 'Auth-Type'],
            ['op' => ':=', 'value' => 'Reject']
        );

        // Disconnect session aktif
        $this->disconnectUser($username);

        Log::info('[RadiusService] suspendUser', ['username' => $username]);
    }

    /**
     * Reactivate user — hapus block, set kembali ke group normal.
     * Menggantikan: ProvisionCustomerJob logic untuk REACTIVATED
     *
     * @param string $username
     * @param string $groupName
     */
    public function reactivateUser(string $username, string $groupName): void
    {
        // Hapus Auth-Type Reject
        RadCheck::where('username', $username)
            ->where('attribute', 'Auth-Type')
            ->delete();

        // Pastikan group mapping benar
        RadUserGroup::updateOrCreate(
            ['username' => $username],
            ['groupname' => $groupName, 'priority' => 1]
        );

        // Disconnect session lama agar reconnect dengan profile baru
        $this->disconnectUser($username);

        Log::info('[RadiusService] reactivateUser', [
            'username' => $username,
            'group'    => $groupName,
        ]);
    }

    /**
     * Cek apakah user sedang aktif (ada session ongoing).
     * Menggantikan: RouterOSService::isUserActive()
     *
     * @param string $username
     * @return bool
     */
    public function isUserActive(string $username): bool
    {
        return RadAcct::where('username', $username)
            ->whereNull('acctstoptime')
            ->exists();
    }

    /**
     * Disconnect session aktif via CoA Disconnect-Request.
     * Menggantikan: RouterOSService::disconnectIfActive()
     *
     * @param string $username
     */
    public function disconnectUser(string $username): void
    {
        // Cari session aktif
        $sessions = RadAcct::where('username', $username)
            ->whereNull('acctstoptime')
            ->get();

        if ($sessions->isEmpty()) {
            Log::info('[RadiusService] disconnectUser: no active session', ['username' => $username]);
            return;
        }

        foreach ($sessions as $session) {
            $nasIp  = $session->nasipaddress;
            // Ambil secret dari tabel nas
            $nas = \DB::connection('radius')
                ->table('nas')
                ->where('nasname', $nasIp)
                ->first();

            if (!$nas) {
                Log::warning('[RadiusService] disconnectUser: NAS not found', [
                    'username' => $username,
                    'nas_ip'   => $nasIp,
                ]);
                continue;
            }

            $secret = $nas->secret;

            // Kirim CoA Disconnect-Request via radclient
            $cmd = sprintf(
                "echo 'User-Name=%s' | radclient -x %s:3799 disconnect %s 2>&1",
                escapeshellarg($username),
                escapeshellarg($nasIp),
                escapeshellarg($secret)
            );

            $output = [];
            $returnCode = 0;
            exec($cmd, $output, $returnCode);

            Log::info('[RadiusService] disconnectUser CoA sent', [
                'username'    => $username,
                'nas_ip'      => $nasIp,
                'return_code' => $returnCode,
                'output'      => implode("\n", $output),
            ]);
        }
    }

    /**
     * Hapus user dari RADIUS (untuk cancel/remove).
     *
     * @param string $username
     */
    public function removeUser(string $username): void
    {
        RadCheck::where('username', $username)->delete();
        RadReply::where('username', $username)->delete();
        RadUserGroup::where('username', $username)->delete();

        Log::info('[RadiusService] removeUser', ['username' => $username]);
    }
}
