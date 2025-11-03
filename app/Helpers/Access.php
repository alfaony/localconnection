<?php

namespace App\Helpers;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Access
{
    private static $requestCache = [];

    public static function can($method, $table)
    {
        $user = Auth::user();
        $roleId = $user->role_id;
        $cacheKey = "role_permissions:{$roleId}";

        // 1. Cek request cache dulu
        if (isset(self::$requestCache[$roleId])) {
            $permissions = self::$requestCache[$roleId];
            return in_array("{$method}:{$table}", $permissions);
        }

        // 2. Cek Redis cache
        $permissions = Cache::get($cacheKey);

        // 3. Load dari DB kalau cache miss
        if ($permissions === null) {
            $role = Role::with('permissions')
                ->where('id', $roleId)
                ->where('guard_name', 'web')
                ->whereNull('deleted_at')
                ->first();

            $permissions = $role ? $role->permissions
                ->map(fn($p) => "{$p->method}:{$p->table}")
                ->toArray() : [];

            Cache::put($cacheKey, $permissions, now()->addHours(24));
        }

        // 4. Simpan ke request cache
        self::$requestCache[$roleId] = $permissions;

        // 5. Return hasil
        return in_array("{$method}:{$table}", $permissions);
    }

    public static function clearCacheForRole($roleId)
    {
        Cache::forget("role_permissions:{$roleId}");
        unset(self::$requestCache[$roleId]);
    }
}