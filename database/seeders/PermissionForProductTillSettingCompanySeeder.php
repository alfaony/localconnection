<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForProductTillSettingCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // const ROOT = 'Super Administrator'; // all access
        // const ADMIN = 'Administrator'; // all except role & permission (his company only)
        // const FINANCE = 'Top Management';
        // const PROCUREMENT = 'Procurement';
        // const PM = 'Project Manager';
        // const HR = 'Human Resource';
        // const SALES = 'Sales';

        // Create Roles
        try {
            $root = Role::where('name',RoleSchema::ROOT)->first();
        } catch (\Exception $e) {
            $root = Role::create([
                'name' => RoleSchema::ROOT,
                'desc' => 'Akun utama developer yang berfungsi untuk mengkontrol seluruh akses',
                'guard_name' => 'web'
            ]);
        }

        try {
            $admin = Role::where('name',RoleSchema::ADMIN)->first();
        } catch (\Exception $e) {
            $admin = Role::create([
                'name' => RoleSchema::ADMIN,
                'desc' => 'Akun utama developer yang berfungsi untuk mengkontrol seluruh akses',
                'guard_name' => 'web'
            ]);
        }

        try {
            $finance = Role::where('name',RoleSchema::FINANCE)->first();
        } catch (\Exception $e) {
            $finance = Role::create([
                'name' => RoleSchema::FINANCE,
                'desc' => 'Akun utama developer yang berfungsi untuk mengkontrol seluruh akses',
                'guard_name' => 'web'
            ]);
        }

        try {
            $procurement = Role::where('name',RoleSchema::PROCUREMENT)->first();
        } catch (\Exception $e) {
            $procurement = Role::create([
                'name' => RoleSchema::PROCUREMENT,
                'desc' => 'Akun utama developer yang berfungsi untuk mengkontrol seluruh akses',
                'guard_name' => 'web'
            ]);
        }

        try {
            $pm = Role::where('name',RoleSchema::PM)->first();
        } catch (\Exception $e) {
            $pm = Role::create([
                'name' => RoleSchema::PM,
                'desc' => 'Akun utama developer yang berfungsi untuk mengkontrol seluruh akses',
                'guard_name' => 'web'
            ]);
        }

        try {
            $hr = Role::where('name',RoleSchema::HR)->first();
        } catch (\Exception $e) {
            $hr = Role::create([
                'name' => RoleSchema::HR,
                'desc' => 'Akun utama developer yang berfungsi untuk mengkontrol seluruh akses',
                'guard_name' => 'web'
            ]);
        }

        try {
            $sales = Role::where('name',RoleSchema::SALES)->first();
        } catch (\Exception $e) {
            $sales = Role::create([
                'name' => RoleSchema::SALES,
                'desc' => 'Akun utama developer yang berfungsi untuk mengkontrol seluruh akses',
                'guard_name' => 'web'
            ]);
        }
        
        // project
        $projects = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2'];

        // Project
        foreach ($projects as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Project',
            ],[
                'method' => $method,
                'table' => 'projects',
                'model' => 'Project',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $pm->id, 'permission_id' => $permission->id]);
        }

        $employees = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2'];

        // Employee
        foreach ($employees as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Employee',
            ],[
                'method' => $method,
                'table' => 'employees',
                'model' => 'Employee',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $hr->id, 'permission_id' => $permission->id]);
        }

        // User
        $users = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2'];

        foreach ($employees as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' User',
            ],[
                'method' => $method,
                'table' => 'users',
                'model' => 'User',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $finance->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $procurement->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $hr->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $sales->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $pm->id, 'permission_id' => $permission->id]);
        }

        // supliers
        $supliers = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'select2','deletePurchase'];

        foreach ($supliers as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Suplier',
            ],[
                'method' => $method,
                'table' => 'supliers',
                'model' => 'Suplier',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $procurement->id, 'permission_id' => $permission->id]);
        }

        // reports
        $reports = ['index'];

        foreach ($reports as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Report',
            ],[
                'method' => $method,
                'table' => 'reports',
                'model' => 'report',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
        }

        // manager
        $managers = ['index','create', 'show', 'edit', 'update', 'destroy', 'store', 'destroyJob','counting'];

        foreach ($managers as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Manager',
            ],[
                'method' => $method,
                'table' => 'managers',
                'model' => 'Manager',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $pm->id, 'permission_id' => $permission->id]);
        }

        // customers
        $customers = ['index','edit', 'update', 'destroy', 'store', 'destroyJob','counting'];

        foreach ($customers as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Customer',
            ],[
                'method' => $method,
                'table' => 'customers',
                'model' => 'Customer',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $sales->id, 'permission_id' => $permission->id]);
        }

        // products
        $products = ['index','edit', 'update', 'destroy', 'store'];

        foreach ($products as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Product',
            ],[
                'method' => $method,
                'table' => 'products',
                'model' => 'Product',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $procurement->id, 'permission_id' => $permission->id]);
        }

        // quote
        $quotes = ['index','edit', 'update', 'create', 'destroy', 'store', 'destroyProduct', 'select2', 'dataTableJson', 'downloadPdf', 'counting', 'productCounting'];

        foreach ($quotes as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Quote',
            ],[
                'method' => $method,
                'table' => 'quotes',
                'model' => 'Quote',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $sales->id, 'permission_id' => $permission->id]);
        }

        // work_orders
        $work_orders = ['index','edit', 'create', 'update', 'destroy', 'store', 'destroyProduct', 'select2', 'dataTableJson', 'downloadPdf', 'counting', 'productCounting' ,'suggestionQuote'];

        foreach ($work_orders as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' WorkOrder',
            ],[
                'method' => $method,
                'table' => 'work_orders',
                'model' => 'WorkOrder',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $sales->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $finance->id, 'permission_id' => $permission->id]);

        }

        // agreement_letters
        $agreement_letters = ['index','edit', 'create', 'show', 'update', 'destroy', 'store', 'downloadPdf', 'dataTableJson'];

        foreach ($agreement_letters as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' AgreementLetter',
            ],[
                'method' => $method,
                'table' => 'agreement_letters',
                'model' => 'AgreementLetter',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $sales->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $pm->id, 'permission_id' => $permission->id]);
        }

        // basts
        $basts = ['index','edit', 'create', 'update', 'show', 'destroy', 'store', 'downloadPdf', 'dataTableJson'];

        foreach ($basts as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Bast',
            ],[
                'method' => $method,
                'table' => 'basts',
                'model' => 'Bast',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $finance->id, 'permission_id' => $permission->id]);
        }

        // report_projects
        $report_projects = ['index','edit', 'update', 'create', 'show', 'destroy', 'store', 'downloadPdf', 'dataTableJson'];

        foreach ($report_projects as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' ReportProject',
            ],[
                'method' => $method,
                'table' => 'report_projects',
                'model' => 'ReportProject',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
        }

        $settingCompany = ['index','store'];

        foreach ($settingCompany as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' SettingCompany',
            ],[
                'method' => $method,
                'table' => 'setting_companies',
                'model' => 'SettingCompany',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);
        }

        $dashboards = ['index','showReport'];

        foreach ($dashboards as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Home',
            ],[
                'method' => $method,
                'table' => 'homes',
                'model' => 'Home',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
            PermissionRole::create(['role_id' => $admin->id, 'permission_id' => $permission->id]);

            if($method != 'showReport')
            {
                PermissionRole::create(['role_id' => $pm->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $sales->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $hr->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $procurement->id, 'permission_id' => $permission->id]);
                PermissionRole::create(['role_id' => $finance->id, 'permission_id' => $permission->id]);
            }
        }
        // roles
        $roles = ['index','edit', 'update', 'show', 'destroy', 'store' ,'create'];

        foreach ($roles as $method) 
        {
            // create permision
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method).' Role',
            ],[
                'method' => $method,
                'table' => 'roles',
                'model' => 'Role',
                'guard_name' => 'web'
            ]);

            //assign role & permission
            PermissionRole::create(['role_id' => $root->id, 'permission_id' => $permission->id]);
        }
    }
}

