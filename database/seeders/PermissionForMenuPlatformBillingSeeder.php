<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Route platform-billing (kelola billing platform semua Company, khusus superadmin).
 *
 * - Menu: table='platform_billing', method='index'
 * - Route access (RolePermission middleware): table='platform_billings'
 *   (dari URL segment "platform-billing" -> plural "platform_billings"),
 *   method 'index' (dari nama akhir route "platform-billing.index")
 */
class PermissionForMenuPlatformBillingSeeder extends Seeder
{
    public function run(): void
    {
        $root = Role::where('name', RoleSchema::ROOT)->first();
        $superAdmin = Role::where('name', RoleSchema::SUPER_ADMIN)->first();

        if (!$root) {
            return;
        }

        DB::transaction(function () use ($root, $superAdmin) {
            $menuPermission = Permission::firstOrCreate(
                ['method' => 'index', 'table' => 'platform_billing', 'guard_name' => 'web'],
                ['name' => 'Index Platform Billing Menu', 'model' => 'PlatformInvoice']
            );
            PermissionRole::firstOrCreate(['role_id' => $root->id, 'permission_id' => $menuPermission->id]);

            $routePermission = Permission::firstOrCreate(
                ['method' => 'index', 'table' => 'platform_billings', 'guard_name' => 'web'],
                ['name' => 'Platform Billing Index', 'model' => 'PlatformInvoice']
            );
            PermissionRole::firstOrCreate(['role_id' => $root->id, 'permission_id' => $routePermission->id]);

            // Halaman lintas-company ini khusus Super Admin platform
            if ($superAdmin) {
                PermissionRole::firstOrCreate(['role_id' => $superAdmin->id, 'permission_id' => $menuPermission->id]);
                PermissionRole::firstOrCreate(['role_id' => $superAdmin->id, 'permission_id' => $routePermission->id]);
            }
        });

        $this->call(ClearPermissionSeeder::class);
    }
}
