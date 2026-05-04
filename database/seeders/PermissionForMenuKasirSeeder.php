<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Schemas\RoleSchema;
use App\Models\Permission;
use App\Models\PermissionRole;

class PermissionForMenuKasirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cari role staff
        $roles = Role::WhereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE,RoleSchema::SYSTEM, RoleSchema::STAFF_FINANCE, RoleSchema::PROCUREMENT])->get();

        DB::beginTransaction();
        try {   

            $roleSales = ['index','edit', 'create', 'update', 'show', 'destroy',"printReceiptManagement"];
            $roleKasir = ['index','sendReceiptByEmail','searchProduct','processPayment','saveDraft','loadDraft','deleteDraft','printReceipt','getDrafts','checkStock'];
            
             foreach ($roleSales as $method) 
             {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Sales (Penjualan)',
                ],[
                    'method' => $method,
                    'table' => 'sales',
                    'model' => 'Sales',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {
                    if($method == 'destroy' && in_array($role->name, [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE, RoleSchema::STAFF_FINANCE]))
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }else
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }
                }
            }


            foreach ($roleKasir as $method) 
            {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Kasir (Kasir)',
                ],[
                    'method' => $method,
                    'table' => 'store_sellings',
                    'model' => 'StoreSelling',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {
                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollBack();
            Log::error($th->getMessage());
        }
    }
}


