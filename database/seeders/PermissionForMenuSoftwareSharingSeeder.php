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

class PermissionForMenuSoftwareSharingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cari role staff
        $roles = Role::WhereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE,RoleSchema::SYSTEM, RoleSchema::STAFF_FINANCE, RoleSchema::PROCUREMENT, RoleSchema::MANAGER, RoleSchema::DIRECTOR])->get();

        DB::beginTransaction();
        try {   
            $softwareDashboard = ['index'];
            $software = ['index','create','store','edit','update','destroy','show','toggleStatus','dashboard','importTemplate','import','importStatus'];
            $softwarePackages = ['index','create','store','edit','update','destroy','show','toggleStatus'];
            $masterAccount = ['index','edit', 'create','store','update', 'show', 'destroy','toggleStatus','customers'];
            $subscriptions = ['index','create','store','edit','update','destroy','show','toggleStatus','editExpiry','updateExpiry','editMasterAccount','updateMasterAccount','suspend','activate','payments','manual-approve','createMarketplace','checkUserEmail','storeMarketplace'];
            
            foreach ($softwareDashboard as $method) 
            {
                // create permision
                $permissionsoftwareDashboard = Permission::firstOrCreate([
                    'name' => ucwords($method).' Dasboard Software (Akun Sharing)',
                ],[
                    'method' => $method,
                    'table' => 'software_dashboards',
                    'model' => 'SoftwareDashboard',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {

                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionsoftwareDashboard->id]);
                }
            }

            foreach ($software as $method) 
            {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Sharing Account Software',
                ],[
                    'method' => $method,
                    'table' => 'software',
                    'model' => 'Software',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {
                    if(($method == 'destroy' || $method == 'manual-approve' || $method == 'suspend' || $method == 'activate') && in_array($role->name, [RoleSchema::DIRECTOR,RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE, RoleSchema::STAFF_FINANCE]))
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }else
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }
                }
            }

            foreach ($softwarePackages as $method) 
            {
                // create permision
                $permissionSoftwarePackages = Permission::firstOrCreate([
                    'name' => ucwords($method).' Sharing Package Software',
                ],[
                    'method' => $method,
                    'table' => 'software_packages',
                    'model' => 'SoftwarePackage',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {
                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionSoftwarePackages->id]);
                }
            }

            foreach ($masterAccount as $method) 
            {
                // create permision
                $permissionmasterAccount = Permission::firstOrCreate([
                    'name' => ucwords($method).' Master Account (Sharing)',
                ],[
                    'method' => $method,
                    'table' => 'master_accounts',
                    'model' => 'MasterAccount',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {
                    if($method == 'destroy' && in_array($role->name, [RoleSchema::DIRECTOR,RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE, RoleSchema::STAFF_FINANCE, RoleSchema::PROCUREMENT, RoleSchema::MANAGER]))
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionmasterAccount->id]);
                    }else
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionmasterAccount->id]);
                    }
                }
            }

            foreach ($subscriptions as $method) 
            {
                // create permision
                $permissionsubscriptions = Permission::firstOrCreate([
                    'name' => ucwords($method).' Subscriptions (Software Sharing)',
                ],[
                    'method' => $method,
                    'table' => 'subscriptions',
                    'model' => 'Subscription',
                    'guard_name' => 'web'
                ]);


                foreach ($roles as $role) 
                {
                    if($method == 'destroy' && in_array($role->name, [RoleSchema::DIRECTOR,RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE, RoleSchema::STAFF_FINANCE, RoleSchema::PROCUREMENT, RoleSchema::MANAGER]))
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionsubscriptions->id]);
                    }else
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionsubscriptions->id]);
                    }
                }
            }

            // foreach ($partnerDaashboard as $method) 
            // {
            //     // create permision
            //     $permission = Permission::firstOrCreate([
            //         'name' => ucwords($method).' Partner Dashboard (Dashboard Mitra)',
            //     ],[
            //         'method' => $method,
            //         'table' => 'partner_dashboards',
            //         'model' => 'PartnerDashboard',
            //         'guard_name' => 'web'
            //     ]);

            //     foreach ($roles as $role) 
            //     {
            //         if($method == 'destroy' && in_array($role->name, [RoleSchema::DIRECTOR,RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE, RoleSchema::STAFF_FINANCE]))
            //         {
            //             PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
            //         }else
            //         {
            //             PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
            //         }
            //     }
            // }

            // foreach ($partner as $method) 
            // {
            //     // create permision
            //     $permission = Permission::firstOrCreate([
            //         'name' => ucwords($method).' Partner (Mitra)',
            //     ],[
            //         'method' => $method,
            //         'table' => 'partners',
            //         'model' => 'Partner',
            //         'guard_name' => 'web'
            //     ]);

            //     foreach ($roles as $role) 
            //     {
            //         if($method == 'destroy' && in_array($role->name, [RoleSchema::DIRECTOR,RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE, RoleSchema::STAFF_FINANCE]))
            //         {
            //             PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
            //         }else
            //         {
            //             PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
            //         }
            //     }
            // }


            $this->call(ClearPermissionSeeder::class);
            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollBack();
            Log::error($th->getMessage());
        }
    }
}



