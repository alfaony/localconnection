<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Route company/domain-settings (settings custom domain per company).
 *
 * - Menu: table='company_domain_settings', method='index'
 * - Route access (RolePermission middleware): table='companies'
 *   (dari URL segment "company" -> plural "companies"), method
 *   'domain-settings' (dari nama akhir route "company.domain-settings")
 */
class PermissionForMenuCompanyDomainSettingsSeeder extends Seeder
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
                ['method' => 'index', 'table' => 'company_domain_settings', 'guard_name' => 'web'],
                ['name' => 'Index Company Domain Settings Menu', 'model' => 'Company']
            );
            PermissionRole::firstOrCreate(['role_id' => $root->id, 'permission_id' => $menuPermission->id]);

            $routePermission = Permission::firstOrCreate(
                ['method' => 'domain-settings', 'table' => 'companies', 'guard_name' => 'web'],
                ['name' => 'Company Domain Settings', 'model' => 'Company']
            );
            PermissionRole::firstOrCreate(['role_id' => $root->id, 'permission_id' => $routePermission->id]);

            // Company biasanya diurus admin masing-masing ISP, bukan cuma root
            if ($admin) {
                PermissionRole::firstOrCreate(['role_id' => $admin->id, 'permission_id' => $menuPermission->id]);
                PermissionRole::firstOrCreate(['role_id' => $admin->id, 'permission_id' => $routePermission->id]);
            }
        });

        $this->call(ClearPermissionSeeder::class);
    }
}
