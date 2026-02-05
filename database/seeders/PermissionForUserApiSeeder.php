<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;

class PermissionForUserApiSeeder extends Seeder
{
    public function run()
    {
        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->command->error('Tidak ada Role ditemukan di database.');
            return;
        }

        $permissions = [
            [
                'table' => 'users',
                'method' => 'indexUsers',
                'name' => 'View all users (API)',
                'guard_name' => 'api'
            ],
            [
                'table' => 'main_projects',
                'method' => 'indexMainProjects',
                'name' => 'View main projects (API)',
                'guard_name' => 'api'
            ],
            [
                'table' => 'projects',
                'method' => 'indexProjects',
                'name' => 'View projects (API)',
                'guard_name' => 'api'
            ],
        ];

        foreach ($permissions as $permData) 
        {
            $permission = Permission::firstOrCreate(
                [
                    'table' => $permData['table'],
                    'method' => $permData['method'],
                ],
                [
                    'name' => $permData['name'],
                    'model' => 'API',
                    'guard_name' => $permData['guard_name']
                ]
            );

            foreach ($roles as $role) 
            {
                $exists = PermissionRole::where('role_id', $role->id)
                                        ->where('permission_id', $permission->id)
                                        ->exists();

                if (!$exists) {
                    PermissionRole::create([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id
                    ]);
                }
            }
        }

        $this->command->info('Permission User berhasil ditambahkan ke semua role.');
    }
}
