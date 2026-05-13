<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;

class PermissionForMomApiSeeder extends Seeder
{
    public function run()
    {
        $this->call(ClearPermissionSeeder::class);
        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->command->error('Tidak ada Role ditemukan.');
            return;
        }

        $permissions = [
            [
                'table' => 'moms', 
                'method' => 'store', 
                'name' => 'Create MoM'
            ],
            [
                'table' => 'moms', 
                'method' => 'storeCustomMoM', 
                'name' => 'Create MoM for Specific User'
            ],
        ];

        foreach ($permissions as $permData) {
            $permission = Permission::firstOrCreate(
                [
                    'table'  => $permData['table'],
                    'method' => $permData['method'],
                ],
                [
                    'name'       => $permData['name'],
                    'model'      => 'API',
                    'guard_name' => 'api'
                ]
            );

            foreach ($roles as $role) {
                PermissionRole::firstOrCreate([
                    'role_id'       => $role->id,
                    'permission_id' => $permission->id
                ]);
            }
        }

        $this->command->info('Permission MoM API berhasil disinkronkan.');
    }
}