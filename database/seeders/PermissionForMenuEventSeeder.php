<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;

class PermissionForMenuEventSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            // ── Event CRUD (Root & Admin) ──────────────────────────────────────
            $crudMethods = ['index', 'create', 'store', 'show','detail','edit', 'update', 'destroy', 'invite', 'removeUser'];
            $crudRoles   = Role::whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN])->get();

            foreach ($crudMethods as $method) {
                $perm = Permission::firstOrCreate(
                    ['name' => ucwords($method) . ' Event'],
                    ['method' => $method, 'table' => 'events', 'model' => 'Event', 'guard_name' => 'web']
                );
                foreach ($crudRoles as $role) {
                    PermissionRole::firstOrCreate(['role_id' => $role->id, 'permission_id' => $perm->id]);
                }
            }

            // ── Home: activeEvents (semua role internal) ──────────────────────
            $homePerm = Permission::firstOrCreate(
                ['name' => 'ActiveEvents Home'],
                ['method' => 'activeEvents', 'table' => 'homes', 'model' => 'Home', 'guard_name' => 'web']
            );

            $homeRoles = Role::whereNotIn('name', [
                RoleSchema::CUSTOMER_SOFTWARE,
                RoleSchema::CUSTOMER_INTERNET,
                RoleSchema::RESIGN,
            ])->get();

            foreach ($homeRoles as $role) {
                PermissionRole::firstOrCreate(['role_id' => $role->id, 'permission_id' => $homePerm->id]);
            }

            $this->call(ClearPermissionSeeder::class);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
        }
    }
}
