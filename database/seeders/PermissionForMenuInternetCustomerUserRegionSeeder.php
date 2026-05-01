<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;

class PermissionForMenuInternetCustomerUserRegionSeeder extends Seeder
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
                    ['name' => ucwords($method) . ' Internet User Region'],
                    ['method' => $method, 'table' => 'internet_customer_user_regions', 'model' => 'InternetCustomerUserRegion', 'guard_name' => 'web']
                );

                foreach ($crudRoles as $role) {
                    PermissionRole::firstOrCreate(['role_id' => $role->id, 'permission_id' => $perm->id]);
                }
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


