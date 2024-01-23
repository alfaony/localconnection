<?php

namespace App\Helpers;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class Access
{
    public static function can($method, $table) {
        $user = Auth::user();
        return Role::byId($user->role_id)->byPermissionName($method, $table)->where('guard_name', 'web')->exists();
    }
}