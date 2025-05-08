<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForMenuInvoice extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $invoices = ['index','edit', 'create', 'update', 'show', 'destroy', 'store', 'downloadPdf', 'dataTableJson','suggestionQuote','productCounting','counting','downloadPdf','select2','productPrice','destroyProduct','downloadPdfA','checkPdfAStatus','clearsessionPdfA'];
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();
        $finance = Role::where('name',RoleSchema::FINANCE)->first();

        foreach ($invoices as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Invoice',
            ],[
                'method' => $method,
                'table' => 'invoices',
                'model' => 'Invoice',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            if ($root) {
                PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            }
            if($finance){
                PermissionRole::create(['role_id' => $finance->id, 'permission_id' => $permission->id]);
            }
            if($admin){
                PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            }
        }
    }
}
