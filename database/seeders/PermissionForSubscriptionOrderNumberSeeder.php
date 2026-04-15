<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;

class PermissionForSubscriptionOrderNumberSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::whereIn('name', [
            RoleSchema::ROOT,
            RoleSchema::ADMIN,
            RoleSchema::SYSTEM,
            RoleSchema::FINANCE,
            RoleSchema::STAFF_FINANCE,
            RoleSchema::PROCUREMENT,
            RoleSchema::MANAGER,
            RoleSchema::DIRECTOR,
        ])->get();

        $methods = ['editOrderNumber', 'updateOrderNumber'];

        DB::beginTransaction();
        try {
            foreach ($methods as $method) {
                $permission = Permission::firstOrCreate(
                    ['name' => ucwords($method) . ' Subscriptions (Software Sharing)'],
                    [
                        'method'     => $method,
                        'table'      => 'subscriptions',
                        'model'      => 'Subscription',
                        'guard_name' => 'web',
                    ]
                );

                foreach ($roles as $role) {
                    PermissionRole::firstOrCreate([
                        'role_id'       => $role->id,
                        'permission_id' => $permission->id,
                    ]);
                }
            }

            $this->call(ClearPermissionSeeder::class);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PermissionForSubscriptionOrderNumberSeeder: ' . $th->getMessage());
            throw $th;
        }
    }
}
