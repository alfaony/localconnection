<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;

class PermissionForMenuMasterGroupSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            // ── Event CRUD (Root & Admin) ──────────────────────────────────────
            $crudMethods = ['index', 'create', 'store', 'show','detail','edit', 'update', 'destroy'];
            $crudRoles   = Role::whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN])->get();
            $showEvent = null;
            foreach ($crudMethods as $method) {
                $perm = Permission::firstOrCreate(
                    ['name' => ucwords($method) . ' Internet Group'],
                    ['method' => $method, 'table' => 'internet_customer_groups', 'model' => 'InternetCustomerGroup', 'guard_name' => 'web']
                );

                if($method == 'show'){
                    $showEvent = $perm;
                }

                foreach ($crudRoles as $role) {
                    PermissionRole::firstOrCreate(['role_id' => $role->id, 'permission_id' => $perm->id]);
                }
            }

            $this->call(ClearPermissionSeeder::class);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
        }
    }
}

