<?php

namespace App\Services;

use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\HotspotVoucher;
use App\Models\Radius\RadCheck;
use App\Models\Radius\RadReply;
use App\Models\Radius\RadGroupReply;
use App\Models\Radius\RadUserGroup;
use App\Models\Radius\RadAcct;
use Illuminate\Support\Facades\Log;

class RadiusService
{
    /**
     * Cek apakah RADIUS enabled
     */
    public static function isEnabled(): bool
    {
        return (bool) config('services.radius.enabled', false);
    }

    /**
     * Pastikan group/profile ada di RADIUS dengan rate-limit yang benar.
     */
    public function ensureGroup(InternetPackage $pkg, string $groupName): void
    {
        $down = (int)($pkg->rate_down_mbps ?? $pkg->bandwidth ?? 0);
        $up   = (int)($pkg->rate_up_mbps ?? max(1, (int)ceil(($pkg->bandwidth ?? 1) * 0.2)));
        $rate = "{$down}M/{$up}M";

        RadGroupReply::updateOrCreate(
            ['groupname' => $groupName, 'attribute' => 'Mikrotik-Rate-Limit'],
            ['op' => ':=', 'value' => $rate]
        );

        Log::info('[RadiusService] ensureGroup', ['group' => $groupName, 'rate' => $rate]);
    }

    /**
     * Hotspot-aware group setup — termasuk Session-Timeout dan Idle-Timeout dari paket.
     * Jika nilai 0 / null → atribut dihapus (unlimited).
     */
    public function ensureHotspotGroup(InternetPackage $pkg, string $groupName): void
    {
        // Rate-limit (sama seperti PPPoE)
        $down = (int)($pkg->rate_down_mbps ?? $pkg->bandwidth ?? 0);
        $up   = (int)($pkg->rate_up_mbps ?? max(1, (int)ceil(($pkg->bandwidth ?? 1) * 0.2)));
        RadGroupReply::updateOrCreate(
            ['groupname' => $groupName, 'attribute' => 'Mikrotik-Rate-Limit'],
            ['op' => ':=', 'value' => "{$down}M/{$up}M"]
        );

        // Session-Timeout (detik). 0 / null = unlimited → hapus atribut.
        $sessionTimeout = (int)($pkg->session_timeout_seconds ?? 0);
        if ($sessionTimeout > 0) {
            RadGroupReply::updateOrCreate(
                ['groupname' => $groupName, 'attribute' => 'Session-Timeout'],
                ['op' => ':=', 'value' => (string)$sessionTimeout]
            );
        } else {
            RadGroupReply::where('groupname', $groupName)->where('attribute', 'Session-Timeout')->delete();
        }

        // Idle-Timeout (detik). 0 / null = unlimited → hapus atribut.
        $idleTimeout = (int)($pkg->idle_timeout_seconds ?? 0);
        if ($idleTimeout > 0) {
            RadGroupReply::updateOrCreate(
                ['groupname' => $groupName, 'attribute' => 'Idle-Timeout'],
                ['op' => ':=', 'value' => (string)$idleTimeout]
            );
        } else {
            RadGroupReply::where('groupname', $groupName)->where('attribute', 'Idle-Timeout')->delete();
        }

        Log::info('[RadiusService] ensureHotspotGroup', [
            'group'           => $groupName,
            'rate'            => "{$down}M/{$up}M",
            'session_timeout' => $sessionTimeout,
            'idle_timeout'    => $idleTimeout,
        ]);
    }

    /**
     * Buat/update user di RADIUS (password + group mapping).
     */
    public function upsertUser(InternetCustomer $cust, string $groupName): void
    {
        $password = trim((string) $cust->pass_hash) ?: 'admin123';

        RadCheck::updateOrCreate(
            ['username' => $cust->username, 'attribute' => 'Cleartext-Password'],
            ['op' => ':=', 'value' => $password]
        );

        RadUserGroup::updateOrCreate(
            ['username' => $cust->username],
            ['groupname' => $groupName, 'priority' => 1]
        );

        if ($cust->local_address) {
            RadReply::updateOrCreate(
                ['username' => $cust->username, 'attribute' => 'Framed-IP-Address'],
                ['op' => ':=', 'value' => $cust->local_address]
            );
        } else {
            RadReply::where('username', $cust->username)
                ->where('attribute', 'Framed-IP-Address')
                ->delete();
        }

        Log::info('[RadiusService] upsertUser', ['username' => $cust->username, 'group' => $groupName]);
    }

    /**
     * Buat/update customer hotspot di RADIUS (password + group + Framed-IP-Address kondisional).
     *
     * Framed-IP-Address:
     *  - ip_binding_type = 'radius' + ip_address terisi → set ke ip_address
     *  - selain itu → hapus (tidak ada fixed IP)
     *
     * ip_binding_type = 'direct' ditangani terpisah via MikroTik /ip/hotspot/ip-binding/.
     */
    public function upsertHotspotCustomer(InternetCustomer $cust, string $groupName): void
    {
        $password = trim((string) $cust->pass_hash) ?: 'admin123';

        RadCheck::updateOrCreate(
            ['username' => $cust->username, 'attribute' => 'Cleartext-Password'],
            ['op' => ':=', 'value' => $password]
        );

        RadUserGroup::updateOrCreate(
            ['username' => $cust->username],
            ['groupname' => $groupName, 'priority' => 1]
        );

        // Radius IP binding → Framed-IP-Address dari ip_address (bukan local_address)
        if ($cust->ip_binding_type === 'radius' && !empty($cust->ip_address)) {
            RadReply::updateOrCreate(
                ['username' => $cust->username, 'attribute' => 'Framed-IP-Address'],
                ['op' => ':=', 'value' => $cust->ip_address]
            );
        } else {
            RadReply::where('username', $cust->username)
                ->where('attribute', 'Framed-IP-Address')
                ->delete();
        }

        Log::info('[RadiusService] upsertHotspotCustomer', [
            'username'        => $cust->username,
            'group'           => $groupName,
            'ip_binding_type' => $cust->ip_binding_type,
            'ip_address'      => $cust->ip_address,
        ]);
    }

    /**
     * Hapus Framed-IP-Address (dipanggil saat suspend radius binding).
     */
    public function removeFramedIp(string $username): void
    {
        RadReply::where('username', $username)->where('attribute', 'Framed-IP-Address')->delete();
    }

    /**
     * Suspend customer HOTSPOT — Auth-Type:Reject (tidak bisa login sama sekali).
     * Hapus group assignment + set Reject. Session di-kick via ProvisionJob.
     * Reactivate: reactivateUser() hapus Reject + restore group.
     */
    public function suspendHotspotCustomer(string $username): void
    {
        // Set Auth-Type = Reject → RADIUS tolak semua auth request
        RadCheck::updateOrCreate(
            ['username' => $username, 'attribute' => 'Auth-Type'],
            ['op' => ':=', 'value' => 'Reject']
        );

        // Hapus group assignment
        RadUserGroup::where('username', $username)->delete();

        Log::info('[RadiusService] suspendHotspotCustomer → Auth-Type:Reject', ['username' => $username]);
    }

    /**
     * Suspend user PPPoE/IPoE — WALLED GARDEN (Isolir)
     * User tetap bisa connect tapi hanya akses web pembayaran.
     * Rate-limit diperkecil, group diganti ke ISOLIR.
     * Mikrotik firewall yang handle domain filtering.
     */
    public function suspendUser(string $username): void
    {
        // Pastikan group ISOLIR ada dengan rate-limit kecil
        RadGroupReply::updateOrCreate(
            ['groupname' => 'ISOLIR', 'attribute' => 'Mikrotik-Rate-Limit'],
            ['op' => ':=', 'value' => '1M/1M']
        );

        // Assign ke pool-suspended yang sudah ada di Mikrotik
        RadGroupReply::updateOrCreate(
            ['groupname' => 'ISOLIR', 'attribute' => 'Framed-Pool'],
            ['op' => ':=', 'value' => 'pool-suspended']
        );

        // Pindahkan user ke group ISOLIR
        RadUserGroup::updateOrCreate(
            ['username' => $username],
            ['groupname' => 'ISOLIR', 'priority' => 1]
        );

        // Hapus Auth-Type Reject jika ada (dari sistem lama)
        RadCheck::where('username', $username)
            ->where('attribute', 'Auth-Type')
            ->delete();

        Log::info('[RadiusService] suspendUser → ISOLIR (walled garden)', ['username' => $username]);
    }

    /**
     * Reactivate user — hapus block, set kembali ke group normal.
     */
    public function reactivateUser(string $username, string $groupName): void
    {
        RadCheck::where('username', $username)
            ->where('attribute', 'Auth-Type')
            ->delete();

        RadUserGroup::updateOrCreate(
            ['username' => $username],
            ['groupname' => $groupName, 'priority' => 1]
        );

        Log::info('[RadiusService] reactivateUser', ['username' => $username, 'group' => $groupName]);
    }

    /**
     * Cek apakah user sedang aktif.
     */
    public function isUserActive(string $username): bool
    {
        return RadAcct::where('username', $username)
            ->whereNull('acctstoptime')
            ->exists();
    }

    /**
     * Hapus user dari RADIUS.
     */
    public function removeUser(string $username): void
    {
        RadCheck::where('username', $username)->delete();
        RadReply::where('username', $username)->delete();
        RadUserGroup::where('username', $username)->delete();

        Log::info('[RadiusService] removeUser', ['username' => $username]);
    }

    // ============================================================
    // VOUCHER METHODS
    // ============================================================

    /**
     * Buat/update voucher hotspot di RADIUS.
     * - radcheck: Cleartext-Password
     * - radreply: Mikrotik-Rate-Limit, Session-Timeout (jika > 0), Mikrotik-Total-Limit (jika quota > 0)
     */
    public function upsertVoucherUser(HotspotVoucher $voucher): void
    {
        $profile  = $voucher->internetPackage;
        $username = $voucher->username;
        $password = $voucher->password;

        $down = (int) $profile->rate_down_mbps;
        $up   = (int) $profile->rate_up_mbps;
        $rate = "{$down}M/{$up}M";

        // Password
        RadCheck::updateOrCreate(
            ['username' => $username, 'attribute' => 'Cleartext-Password'],
            ['op' => ':=', 'value' => $password]
        );

        // Rate limit
        RadReply::updateOrCreate(
            ['username' => $username, 'attribute' => 'Mikrotik-Rate-Limit'],
            ['op' => ':=', 'value' => $rate]
        );

        // Session-Timeout (waktu dalam detik)
        if ($profile->session_timeout_seconds > 0) {
            RadReply::updateOrCreate(
                ['username' => $username, 'attribute' => 'Session-Timeout'],
                ['op' => ':=', 'value' => (string) $profile->session_timeout_seconds]
            );
        } else {
            RadReply::where('username', $username)->where('attribute', 'Session-Timeout')->delete();
        }

        // Mikrotik-Total-Limit (data quota dalam bytes)
        if ($profile->quota_bytes > 0) {
            RadReply::updateOrCreate(
                ['username' => $username, 'attribute' => 'Mikrotik-Total-Limit'],
                ['op' => ':=', 'value' => (string) $profile->quota_bytes]
            );
        } else {
            RadReply::where('username', $username)->where('attribute', 'Mikrotik-Total-Limit')->delete();
        }

        Log::info('[RadiusService] upsertVoucherUser', [
            'username' => $username,
            'rate'     => $rate,
            'timeout'  => $profile->session_timeout_seconds,
            'quota'    => $profile->quota_bytes,
        ]);
    }

    /**
     * Hapus voucher user dari RADIUS.
     */
    public function removeVoucherUser(string $username): void
    {
        RadCheck::where('username', $username)->delete();
        RadReply::where('username', $username)->delete();
        RadUserGroup::where('username', $username)->delete();

        Log::info('[RadiusService] removeVoucherUser', ['username' => $username]);
    }
}
