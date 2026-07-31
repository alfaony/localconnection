<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionForMenuOltSeeder extends Seeder
{
    public function run(): void
    {
        $root = Role::where('name', RoleSchema::ROOT)->first();

        if (!$root) {
            return;
        }

        DB::transaction(function () use ($root) {
            foreach (['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'] as $method) {
                $permission = Permission::firstOrCreate(
                    [
                        'method' => $method,
                        'table' => 'olts',
                        'guard_name' => 'web',
                    ],
                    [
                        'name' => ucwords($method).' OLT',
                        'model' => 'Olt',
                    ]
                );

                PermissionRole::firstOrCreate([
                    'role_id' => $root->id,
                    'permission_id' => $permission->id,
                ]);
            }
        });

        $this->call(ClearPermissionSeeder::class);
    }
}
