<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;

class PermissionForMeetingApiSeeder extends Seeder
{
    public function run()
    {
        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->command->error('Tidak ada Role ditemukan di database.');
            return;
        }

        $permissions = [
            ['table' => 'meetings', 'method' => 'index', 'name' => 'View all meetings (API)', 'guard_name' => 'api'],
            ['table' => 'meetings', 'method' => 'show', 'name' => 'View meeting detail (API)', 'guard_name' => 'api'],
            ['table' => 'meetings', 'method' => 'store', 'name' => 'Create meeting (API)', 'guard_name' => 'api'],
            ['table' => 'meetings', 'method' => 'update', 'name' => 'Update meeting (API)', 'guard_name' => 'api'],
            ['table' => 'meetings', 'method' => 'destroy', 'name' => 'Delete meeting (API)', 'guard_name' => 'api'],
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

        $this->command->info('Permission API Meeting berhasil ditambahkan ke semua role.');
    }
}
