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

        $permissions = Cache::rememberForever($cacheKey, function () use ($roleId) {
            $role = Role::with(['permissions'])->find($roleId);
            return $role->permissions
                ->map(fn ($perm) => "{$perm->method}:{$perm->table}")
                ->toArray();
        });

        return in_array("{$method}:{$table}", $permissions);
    }

    public static function clearCacheForRole($roleId)
    {
        Cache::forget("role_permissions:{$roleId}");
    }
}