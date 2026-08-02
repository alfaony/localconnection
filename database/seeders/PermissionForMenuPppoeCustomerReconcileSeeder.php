<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sama pola dengan PermissionForMenuStaticCustomerReconcileSeeder — 2
 * permission terpisah untuk mekanisme menu vs route authorization.
 * table='pppoe_customers' (dari URL segment "pppoe-customer"), method
 * 'reconcile' (dari nama akhir route "pppoe-customer.reconcile").
 */
class PermissionForMenuPppoeCustomerReconcileSeeder extends Seeder
{
    public function run(): void
    {
        $root = Role::where('name', RoleSchema::ROOT)->first();

        if (!$root) {
            return;
        }

        DB::transaction(function () use ($root) {
            $menuPermission = Permission::firstOrCreate(
                ['method' => 'index', 'table' => 'pppoe_customer_reconciles', 'guard_name' => 'web'],
                ['name' => 'Index PPPoE Customer Reconcile Menu', 'model' => 'PppoeReconcile']
            );
            PermissionRole::firstOrCreate(['role_id' => $root->id, 'permission_id' => $menuPermission->id]);

            $routePermission = Permission::firstOrCreate(
                ['method' => 'reconcile', 'table' => 'pppoe_customers', 'guard_name' => 'web'],
                ['name' => 'Reconcile PPPoE Customers', 'model' => 'InternetCustomer']
            );
            PermissionRole::firstOrCreate(['role_id' => $root->id, 'permission_id' => $routePermission->id]);
        });

        $this->call(ClearPermissionSeeder::class);
    }
}
