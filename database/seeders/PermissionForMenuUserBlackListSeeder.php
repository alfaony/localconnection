<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;

class PermissionForMenuUserBlackListSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            // ── Event CRUD (Root & Admin) ──────────────────────────────────────
            $crudMethods = ['index', 'create', 'store', 'show','detail','edit', 'update', 'destroy','search','import_inactive'];
            $crudRoles   = Role::whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN])->get();
            $showEvent = null;
            foreach ($crudMethods as $method) {
                $perm = Permission::firstOrCreate(
                    ['name' => ucwords($method) . ' User Blacklist'],
                    ['method' => $method, 'table' => 'user_blacklists', 'model' => 'UserBlacklist', 'guard_name' => 'web']
                );

                foreach ($crudRoles as $role) {
                    PermissionRole::firstOrCreate(['role_id' => $role->id, 'permission_id' => $perm->id]);
                }
            }

             $otherRoles   = Role::whereNotIn('name', [RoleSchema::RESIGN, RoleSchema::CUSTOMER_INTERNET, RoleSchema::CUSTOMER_SOFTWARE])->get();
             $method = 'search';
             foreach ($otherRoles as $role) {
                $permAssign = Permission::firstOrCreate(
                    ['name' => ucwords($method) . ' User'],
                    ['method' => $method, 'table' => 'users', 'model' => 'User', 'guard_name' => 'web']
                );

                PermissionRole::firstOrCreate(['role_id' => $role->id, 'permission_id' => $permAssign->id]);
            }

            DB::commit();
            $this->call(ClearPermissionSeeder::class);
        } catch (\Throwable $th) {
            // dd($th);
            DB::rollBack();
            Log::error($th->getMessage());
        }
    }
}

