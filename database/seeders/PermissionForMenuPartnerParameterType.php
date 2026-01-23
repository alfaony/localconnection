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

class PermissionForMenuPartnerParameterType extends Seeder
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
            $partnerDaashboard = ['dashboard','api'];
            $partner = ['index','create','store','edit','update','destroy','show'];
            $partnerType = ['index','edit', 'create', 'update', 'show', 'destroy','toggleActive','store'];
            $partnerTarget = ['index','create','store','edit','update','destroy'];
            $partnerMonthlyReport = ['manage','create','store','edit','update','destroy'];
            
            foreach ($partnerType as $method) 
            {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Partner Parameter Type (Jenis Parameter Mitra)',
                ],[
                    'method' => $method,
                    'table' => 'partner_parameter_types',
                    'model' => 'PartnerParameterType',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {
                    if($method == 'destroy' && in_array($role->name, [RoleSchema::DIRECTOR,RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE, RoleSchema::STAFF_FINANCE]))
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }else
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }
                }
            }

            foreach ($partnerTarget as $method) 
            {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Partner Target (Target Mitra)',
                ],[
                    'method' => $method,
                    'table' => 'partner_targets',
                    'model' => 'PartnerTarget',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {
                    if($method == 'destroy' && in_array($role->name, [RoleSchema::DIRECTOR,RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE, RoleSchema::STAFF_FINANCE]))
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }else
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }
                }
            }

            foreach ($partnerMonthlyReport as $method) 
            {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Partner Monthly Report (Laporan Bulanan Mitra)',
                ],[
                    'method' => $method,
                    'table' => 'partner_monthly_reports',
                    'model' => 'PartnerMonthlyReport',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {
                    if($method == 'destroy' && in_array($role->name, [RoleSchema::DIRECTOR,RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE, RoleSchema::STAFF_FINANCE]))
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }else
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }
                }
            }

            foreach ($partnerDaashboard as $method) 
            {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Partner Dashboard (Dashboard Mitra)',
                ],[
                    'method' => $method,
                    'table' => 'partner_dashboards',
                    'model' => 'PartnerDashboard',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {
                    if($method == 'destroy' && in_array($role->name, [RoleSchema::DIRECTOR,RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE, RoleSchema::STAFF_FINANCE]))
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }else
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }
                }
            }

            foreach ($partner as $method) 
            {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Partner (Mitra)',
                ],[
                    'method' => $method,
                    'table' => 'partners',
                    'model' => 'Partner',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {
                    if($method == 'destroy' && in_array($role->name, [RoleSchema::DIRECTOR,RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::SYSTEM, RoleSchema::FINANCE, RoleSchema::STAFF_FINANCE]))
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }else
                    {
                        PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }
                }
            }


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


