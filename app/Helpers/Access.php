<?php

namespace App\Helpers;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Access
{
    private static $requestCache = [];

    public static function can($method, $table)
    {
        try {
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

                try {
                    Cache::put($cacheKey, $permissions, now()->addHours(24));
                } catch (\Throwable $cacheError) {
                    // Cache can fail on read-only filesystems; log and continue with in-memory cache.
                    Log::warning('Gagal menulis cache role permissions', [
                        'role_id' => $roleId,
                        'cache_key' => $cacheKey,
                        'error' => $cacheError->getMessage(),
                    ]);
                }
            }
    
            // 4. Simpan ke request cache
            self::$requestCache[$roleId] = $permissions;
    
            // 5. Return hasil
            return in_array("{$method}:{$table}", $permissions);
        } catch (\Exception $th) {
            Log::error($th);
            throw $th;
        }
    }

    public static function clearCacheForRole($roleId)
    {
        Cache::forget("role_permissions:{$roleId}");
        unset(self::$requestCache[$roleId]);
    }
}
