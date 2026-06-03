<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;

class PermissionForHomeDashboardSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();
        try {
            $roles = Role::whereIn('name', [
                RoleSchema::ROOT,
                RoleSchema::ADMIN,
                RoleSchema::MANAGER,
                RoleSchema::STAFF,
                RoleSchema::STAFF_FINANCE,
                RoleSchema::MANAGER_FINANCE,
                RoleSchema::TECKNICIAN_INTERNET,
                RoleSchema::DIRECTOR,
            ])->get()->keyBy('name');

            $permission = Permission::firstOrCreate(
                ['name' => 'View Home Internet Report'],
                [
                    'method'     => 'internetReport',
                    'table'      => 'homes',
                    'model'      => 'Home',
                    'guard_name' => 'web',
                ]
            );

            $assignTo = [
                RoleSchema::ROOT,
                RoleSchema::ADMIN,
                RoleSchema::MANAGER,
                RoleSchema::STAFF,
                RoleSchema::STAFF_FINANCE,
                RoleSchema::MANAGER_FINANCE,
                RoleSchema::DIRECTOR,
            ];

            foreach ($assignTo as $roleName) {
                $role = $roles->get($roleName);
                if (!$role) continue;

                $exists = PermissionRole::where('role_id', $role->id)
                    ->where('permission_id', $permission->id)
                    ->exists();

                if (!$exists) {
                    PermissionRole::create([
                        'role_id'       => $role->id,
                        'permission_id' => $permission->id,
                    ]);
                }
            }

            $this->command->info('Permission "View Home Internet Report" berhasil dibuat dan di-assign.');
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PermissionForHomeDashboardSeeder: ' . $th->getMessage());
            $this->command->error($th->getMessage());
        }
    }
}
