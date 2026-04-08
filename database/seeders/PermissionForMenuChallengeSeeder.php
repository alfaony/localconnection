<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;

class PermissionForMenuChallengeSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            // ── Challenge CRUD (Root & Admin) ──────────────────────────────
            $crudMethods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'invite', 'removeUser'];
            $crudRoles   = Role::whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN])->get();

            foreach ($crudMethods as $method) {
                $perm = Permission::firstOrCreate(
                    ['name' => ucwords($method) . ' Challenge'],
                    ['method' => $method, 'table' => 'challenges', 'model' => 'Challenge', 'guard_name' => 'web']
                );
                foreach ($crudRoles as $role) {
                    PermissionRole::firstOrCreate(['role_id' => $role->id, 'permission_id' => $perm->id]);
                }
            }

            // ── Home: activeChallenges (semua role utama) ──────────────────
            $homePerm = Permission::firstOrCreate(
                ['name' => 'ActiveChallenges Home'],
                ['method' => 'activeChallenges', 'table' => 'homes', 'model' => 'Home', 'guard_name' => 'web']
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
