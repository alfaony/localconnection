<?php

namespace App\Helpers;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Access
{
    public static function can($method, $table)
    {
        $user = Auth::user();
        $roleId = $user->role_id;
        $cacheKey = "role_permissions:{$roleId}";

        // Ambil dari cache
        $permissions = Cache::get($cacheKey);

        // Jika belum ada di cache, atau permission tidak ditemukan
        if (!$permissions || !in_array("{$method}:{$table}", $permissions)) {
            // Ambil langsung dari DB (cek eksistensi)
            $exists = Role::byId($roleId)
                ->byPermissionName($method, $table)
                ->where('guard_name', 'web')
                ->exists();

            // Jika ada, update ulang cache
            if ($exists) {
                $role = Role::with(['permissions'])->find($roleId);
                $permissions = $role->permissions
                    ->map(fn ($perm) => "{$perm->method}:{$perm->table}")
                    ->toArray();

                Cache::forever($cacheKey, $permissions);
            }

            return $exists;
        }

        // Jika ditemukan di cache
        return true;
    }

    public static function clearCacheForRole($roleId)
    {
        Cache::forget("role_permissions:{$roleId}");
    }
}