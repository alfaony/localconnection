<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;

class PermissionForInternetAssetReportSeeder extends Seeder
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
                RoleSchema::DIRECTOR,
            ])->get()->keyBy('name');

            $permissions = [
                // Asset
                ['name' => 'Index Internet Asset',  'method' => 'index',  'table' => 'internet_assets'],
                ['name' => 'Create Internet Asset',  'method' => 'create', 'table' => 'internet_assets'],
                ['name' => 'Store Internet Asset',   'method' => 'store',  'table' => 'internet_assets'],
                ['name' => 'Edit Internet Asset',    'method' => 'edit',   'table' => 'internet_assets'],
                ['name' => 'Update Internet Asset',  'method' => 'update', 'table' => 'internet_assets'],
                ['name' => 'Destroy Internet Asset', 'method' => 'destroy','table' => 'internet_assets'],
                // Report
                ['name' => 'View Internet Report',   'method' => 'index',        'table' => 'internet_reports'],
                ['name' => 'Data Internet Report',   'method' => 'internetReport','table' => 'internet_reports'],
            ];

            $roleAssign = [
                RoleSchema::ROOT,
                RoleSchema::ADMIN,
                RoleSchema::MANAGER,
                RoleSchema::STAFF,
                RoleSchema::STAFF_FINANCE,
                RoleSchema::MANAGER_FINANCE,
                RoleSchema::DIRECTOR,
            ];

            foreach ($permissions as $pData) {
                $permission = Permission::firstOrCreate(
                    ['name' => $pData['name']],
                    [
                        'method'     => $pData['method'],
                        'table'      => $pData['table'],
                        'model'      => 'InternetAsset',
                        'guard_name' => 'web',
                    ]
                );

                foreach ($roleAssign as $roleName) {
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
            }

            $this->command->info('Permissions untuk Internet Asset & Report berhasil dibuat.');
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PermissionForInternetAssetReportSeeder: ' . $th->getMessage());
            $this->command->error($th->getMessage());
        }
    }
}
