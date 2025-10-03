<?php

namespace App\Helpers;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Access
{
    /**
     * Cek apakah user bisa melakukan aksi pada tabel tertentu
     */
    public static function can($method, $table)
    {
        $user = Auth::user();
        $roleId = $user->role_id;
        $cacheKey = "role_permissions:{$roleId}";

        // Ambil permission dari cache
        $permissions = Cache::get($cacheKey);

        // Jika tidak ada di cache atau belum punya akses untuk method:table
        if (!$permissions || !in_array("{$method}:{$table}", $permissions)) 
            {
            // Cek apakah role terkait punya permission tersebut
            $exists = Role::where('id', $roleId)
                ->where('guard_name', 'web')
                ->whereNull('deleted_at')
                ->whereHas('permissions', function ($q) use ($method, $table) {
                    $q->where('method', $method)->where('table', $table);
                })
                ->exists();

            // Jika role punya akses, update cache
            if ($exists) {
                $role = Role::with('permissions')->find($roleId);
                $permissions = $role->permissions
                    ->map(fn($perm) => "{$perm->method}:{$perm->table}")
                    ->toArray();

                Cache::forever($cacheKey, $permissions);
            }

            return $exists;
        }

        // Sudah ada di cache, dan akses valid
        return true;
    }

    /**
     * Hapus cache permission untuk role tertentu
     */
    public static function clearCacheForRole($roleId)
    {
        Cache::forget("role_permissions:{$roleId}");
    }
}