<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Route static-customer/reconcile butuh 2 permission BEDA (2 mekanisme
 * pengecekan berbeda di kode, jangan salah satu aja):
 *
 * 1. table='static_customer_reconciles', method='index'
 *    -> dipakai buat nampilin/nyembunyiin menu sidebar
 *    (lihat AppServiceProvider::$buildSubmenu, Access::can('index', $menuKey))
 *
 * 2. table='static_customers', method='reconcile'
 *    -> dipakai middleware RolePermission buat authorize akses route-nya
 *    (table diturunkan dari URL segment pertama "static-customer" ->
 *    underscore -> plural = "static_customers"; method dari nama akhir
 *    route "static-customer.reconcile" -> "reconcile")
 *
 * Tanpa permission #2, halaman akan 403 meskipun menu-nya keliatan di
 * sidebar (karena #1 doang yang terpenuhi).
 */
class PermissionForMenuStaticCustomerReconcileSeeder extends Seeder
{
    public function run(): void
    {
        $root = Role::where('name', RoleSchema::ROOT)->first();

        if (!$root) {
            return;
        }

        DB::transaction(function () use ($root) {
            $menuPermission = Permission::firstOrCreate(
                ['method' => 'index', 'table' => 'static_customer_reconciles', 'guard_name' => 'web'],
                ['name' => 'Index Static Customer Reconcile Menu', 'model' => 'StaticReconcile']
            );
            PermissionRole::firstOrCreate(['role_id' => $root->id, 'permission_id' => $menuPermission->id]);

            $routePermission = Permission::firstOrCreate(
                ['method' => 'reconcile', 'table' => 'static_customers', 'guard_name' => 'web'],
                ['name' => 'Reconcile Static Customers', 'model' => 'InternetCustomer']
            );
            PermissionRole::firstOrCreate(['role_id' => $root->id, 'permission_id' => $routePermission->id]);
        });

        $this->call(ClearPermissionSeeder::class);
    }
}
