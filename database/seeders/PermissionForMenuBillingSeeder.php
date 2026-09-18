<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Route billing (billing platform Keloola BOS -> Company, self-service sisi Company).
 *
 * - Menu: table='billing', method='index'
 * - Route access (RolePermission middleware): table='billings'
 *   (dari URL segment "billing" -> plural "billings"), method 'index'
 *   (dari nama akhir route "billing.index")
 */
class PermissionForMenuBillingSeeder extends Seeder
{
    public function run(): void
    {
        $root = Role::where('name', RoleSchema::ROOT)->first();
        $admin = Role::where('name', RoleSchema::ADMIN)->first();

        if (!$root) {
            return;
        }

        DB::transaction(function () use ($root, $admin) {
            $menuPermission = Permission::firstOrCreate(
                ['method' => 'index', 'table' => 'billing', 'guard_name' => 'web'],
                ['name' => 'Index Billing Menu', 'model' => 'PlatformInvoice']
            );
            PermissionRole::firstOrCreate(['role_id' => $root->id, 'permission_id' => $menuPermission->id]);

            $routePermission = Permission::firstOrCreate(
                ['method' => 'index', 'table' => 'billings', 'guard_name' => 'web'],
                ['name' => 'Billing Index', 'model' => 'PlatformInvoice']
            );
            PermissionRole::firstOrCreate(['role_id' => $root->id, 'permission_id' => $routePermission->id]);

            // Company (lewat Admin-nya) yang harus lihat & bayar tagihan platform sendiri
            if ($admin) {
                PermissionRole::firstOrCreate(['role_id' => $admin->id, 'permission_id' => $menuPermission->id]);
                PermissionRole::firstOrCreate(['role_id' => $admin->id, 'permission_id' => $routePermission->id]);
            }
        });

        $this->call(ClearPermissionSeeder::class);
    }
}
