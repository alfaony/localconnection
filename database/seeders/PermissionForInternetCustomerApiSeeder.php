<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;

class PermissionForInternetCustomerApiSeeder extends Seeder
{
    public function run()
    {
        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->command->error('Tidak ada Role ditemukan di database.');
            return;
        }

        $permissions = [
            ['table' => 'internet_customers', 'method' => 'index', 'name' => 'View list internet customers (API)'],
            ['table' => 'internet_customers', 'method' => 'show', 'name' => 'View detail internet customer (API)'],
            ['table' => 'internet_customers', 'method' => 'approve', 'name' => 'Approve internet customer (API)'],
            ['table' => 'internet_customers', 'method' => 'close', 'name' => 'Close internet customer (API)'],
            ['table' => 'internet_customers', 'method' => 'completeInstallation', 'name' => 'Complete installation internet customer (API)'],
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
                $exists = PermissionRole::where('role_id', $role->id)
                                        ->where('permission_id', $permission->id)
                                        ->exists();

                if (!$exists) {
                    PermissionRole::create([
                        'role_id'       => $role->id,
                        'permission_id' => $permission->id
                    ]);
                }
            }
        }

        $this->command->info('Permission API Internet Customer berhasil ditambahkan ke semua role.');
    }
}
