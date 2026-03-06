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
     * Suspend user — block authentication.
     */
    public function suspendUser(string $username): void
    {
        RadCheck::updateOrCreate(
            ['username' => $username, 'attribute' => 'Auth-Type'],
            ['op' => ':=', 'value' => 'Reject']
        );

        Log::info('[RadiusService] suspendUser', ['username' => $username]);
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
}
