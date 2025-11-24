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

class PermissionForMenuInternetCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cari role staff
        $admin = Role::where('name', RoleSchema::ADMIN)->first();
        $root = Role::where('name', RoleSchema::ROOT)->first();
        $finance = Role::where('name', RoleSchema::FINANCE)->first();
        $manager = Role::where('name', RoleSchema::MANAGER)->first();
        $staffFinance = Role::where('name', RoleSchema::STAFF_FINANCE)->first();
        $managerFinance = Role::where('name', RoleSchema::MANAGER_FINANCE)->first();
        
        $staffRole = Role::where('name', RoleSchema::STAFF)->first();
        $tecknicianRole = Role::where('name', RoleSchema::TECKNICIAN_INTERNET)->first();

        $customerInternetRole = Role::where('name', RoleSchema::CUSTOMER_INTERNET)->first();

        DB::beginTransaction();
        try {
            if (!$staffRole) 
            {
                $this->command->error('Role STAFF tidak ditemukan.');
                return;
            }

            if (!$customerInternetRole) 
            {
                $customerInternetRole = Role::create([
                    'name' => RoleSchema::CUSTOMER_INTERNET,
                    'desc' => 'Akun Internet Customer',
                    'guard_name' => 'web'
                ]);
            }
            
            if(!$tecknicianRole && $staffRole)
            {
                // Cek apakah role SPRINTER sudah ada
                $tecknicianRole = Role::create([
                    'name' => RoleSchema::TECKNICIAN_INTERNET,
                    'desc' => 'Akun Sprinter',
                    'guard_name' => 'web'
                ]);
        
                // Copy permission dari role staff
                foreach ($staffRole->permissions as $permission) 
                {
                    PermissionRole::create(['role_id' => $tecknicianRole->id, 'permission_id' => $permission->id]);
                }

                $this->command->info('Role SPRINTER berhasil disinkronisasi dengan permission dari STAFF.');
            }
            
            if (!$tecknicianRole) 
            {
                $this->command->error('Role SPRINTER tidak ditemukan.');
                return;
            }

            $itemCustomerInternet = ['index','edit', 'create', 'update', 'show', 'destroy', 'store', 'select2','workflow','dataTableJson','delivery','as_technician','as_finance','closed','editInstalasi','moveRouter','editPackage'];
            $chatMessage = ['index','store','show','edit'];
            
            
             foreach ($itemCustomerInternet as $method) 
             {
                // create permision
                $permission = Permission::firstOrCreate([
                    'name' => ucwords($method).' Internet Customer',
                ],[
                    'method' => $method,
                    'table' => 'internet_customers',
                    'model' => 'InternetCustomer',
                    'guard_name' => 'web'
                ]);
                if (in_array($method, ['as_technician','complete','closed','editInstalasi','moveRouter','editPackage'])) 
                {
                    PermissionRole::create(['role_id' => $tecknicianRole->id, 'permission_id' => $permission->id]);
                    continue;
                }
    
                if (in_array($method, ['as_finance'])) 
                {
                    if ($root) 
                    {
                        PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                    }
                    if($admin){
                        PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                    }
                    if ($staffFinance) 
                    {
                        PermissionRole::create(['role_id' => $staffFinance->id, 'permission_id' => $permission->id]);
                    }
                    if ($managerFinance) 
                    {
                        PermissionRole::create(['role_id' => $managerFinance->id, 'permission_id' => $permission->id]);
                    }
                    if($finance)
                    {
                        PermissionRole::create(['role_id' => $finance->id, 'permission_id' => $permission->id]);
                    }

                    continue;
                }
                //assign role & permission
                if ($root) 
                {
                    PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
                }
                if($admin){
                    PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
                }
                if($finance){
                    PermissionRole::create(['role_id' => $finance->id, 'permission_id' => $permission->id]);
                }
                if ($staffFinance) 
                {
                    PermissionRole::create(['role_id' => $staffFinance->id, 'permission_id' => $permission->id]);
                }
                if ($managerFinance) 
                {
                    PermissionRole::create(['role_id' => $managerFinance->id, 'permission_id' => $permission->id]);
                }
                if($manager)
                {
                    PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permission->id]);
                }
    
                PermissionRole::create(['role_id' => $tecknicianRole->id, 'permission_id' => $permission->id]);
                }
            
            $this->command->info('Berhasil Membuat dan Assign Role Sprinter untuk Internet Customer.');


            // foreach($chatMessage as $method)
            // {
            //     // create permision
            //     $permissionChatMessage = Permission::firstOrCreate([
            //         'name' => ucwords($method).' Chat Message',
            //     ],[
            //         'method' => $method,
            //         'table' => 'chat_messages',
            //         'model' => 'ChatMessage',
            //         'guard_name' => 'web'
            //     ]);


            //     if ($root) 
            //     {
            //         PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permissionChatMessage->id]);
            //     }
            //     if($admin){
            //         PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permissionChatMessage->id]);
            //     }
            //     if($finance){
            //         PermissionRole::create(['role_id' => $finance->id, 'permission_id' => $permissionChatMessage->id]);
            //     }
            //     if ($staffFinance) 
            //     {
            //         PermissionRole::create(['role_id' => $staffFinance->id, 'permission_id' => $permissionChatMessage->id]);
            //     }
            //     if ($managerFinance) 
            //     {
            //         PermissionRole::create(['role_id' => $managerFinance->id, 'permission_id' => $permissionChatMessage->id]);
            //     }
            //     if($manager)
            //     {
            //         PermissionRole::create(['role_id' => $manager->id, 'permission_id' => $permissionChatMessage->id]);
            //     }
    
            //     PermissionRole::create(['role_id' => $tecknicianRole->id, 'permission_id' => $permissionChatMessage->id]);
                
            // }

            // $this->command->info('Berhasil Membuat dan Assign Role Sprinter untuk Chat Message.');
            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollBack();
            Log::error($th->getMessage());
        }
    }
}


