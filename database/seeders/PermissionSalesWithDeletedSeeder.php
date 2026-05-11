<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;

class PermissionSalesWithDeletedSeeder extends Seeder
{
    public function run()
    {
        $roles = Role::whereIn('name', [
            RoleSchema::ROOT,
            RoleSchema::ADMIN,
            RoleSchema::SYSTEM,
        ])->get();

        DB::beginTransaction();
        try {
            $permission = Permission::firstOrCreate(
                ['name' => 'Index WithDeleted Sales (Penjualan Terhapus)'],
                [
                    'method'     => 'index_withdeleted',
                    'table'      => 'sales',
                    'model'      => 'Sale',
                    'guard_name' => 'web',
                ]
            );

            foreach ($roles as $role) {
                PermissionRole::firstOrCreate([
                    'role_id'       => $role->id,
                    'permission_id' => $permission->id,
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
        }
    }
}
