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

class PermissionForMenuCustomerSoftware extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::beginTransaction();
        try {   

            $softwareDashboard = ['softwareSharing'];
            $customerSoftware = ['index','show'];
            $customerCheckout = ['process','show','paymentPending','paymentSuccess','paymentFailed','cancelPending','resumePayment','retryPayment'];
            $customerSubscription = ['index','show','renew','processRenewal','payments','store','cancelRenewalPayment','resumeRenewalPayment'];
            $subscriptionPayment = ['success','failed','uploadProof','checkStatus'];


            $customerSoftwareRole = Role::where('name', RoleSchema::CUSTOMER_SOFTWARE)->first();

            if (!$customerSoftwareRole) 
            {
                $customerSoftwareRole = Role::create([
                    'name' => RoleSchema::CUSTOMER_SOFTWARE,
                    'desc' => 'Akun Software Customer',
                    'guard_name' => 'web'
                ]);
            }
            
            $roles = Role::all();

            foreach ($softwareDashboard as $method) 
            {
                // create permision
                $permissionsoftwareDashboard = Permission::firstOrCreate([
                    'name' => ucwords($method).' Software Dashboard Sharing',
                ],[
                    'method' => $method,
                    'table' => 'homes',
                    'model' => 'Home',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {

                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionsoftwareDashboard->id]);
                }
            }

            foreach ($customerSoftware as $method) 
            {
                // create permision
                $permissionsoftwareDashboard = Permission::firstOrCreate([
                    'name' => ucwords($method).' Customer Software List',
                ],[
                    'method' => $method,
                    'table' => 'customer_software',
                    'model' => 'CustomerSoftwar',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {

                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionsoftwareDashboard->id]);
                }
            }

            foreach ($customerCheckout as $method) 
            {
                // create permision
                $permissionsoftwareDashboard = Permission::firstOrCreate([
                    'name' => ucwords($method).' Customer Software Checkout',
                ],[
                    'method' => $method,
                    'table' => 'customer_checkouts',
                    'model' => 'CustomerCheckout',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {

                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionsoftwareDashboard->id]);
                }
            }

            foreach ($customerSubscription as $method) 
            {
                // create permision
                $permissionCustomerSubscription = Permission::firstOrCreate([
                    'name' => ucwords($method).' Customer Software Subscription',
                ],[
                    'method' => $method,
                    'table' => 'customer_subscriptions',
                    'model' => 'CustomerSubscription',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {

                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionCustomerSubscription->id]);
                }
            }

            foreach ($subscriptionPayment as $method) 
            {
                // create permision
                $permissionsoftwareDashboard = Permission::firstOrCreate([
                    'name' => ucwords($method).' Subscription Payment',
                ],[
                    'method' => $method,
                    'table' => 'subscription_payments',
                    'model' => 'SubscriptionPayment',
                    'guard_name' => 'web'
                ]);

                foreach ($roles as $role) 
                {

                    PermissionRole::create(['role_id' => $role->id, 'permission_id' => $permissionsoftwareDashboard->id]);
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




