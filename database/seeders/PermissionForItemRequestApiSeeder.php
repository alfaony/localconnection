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

class PermissionForItemRequestApiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ClearPermissionSeeder::class);

        $allowedRoles = [
            RoleSchema::ADMIN,
            RoleSchema::ROOT,
            RoleSchema::FINANCE,
            RoleSchema::MANAGER,
            RoleSchema::STAFF_FINANCE,
            RoleSchema::MANAGER_FINANCE,
        ];

        $roles = Role::whereIn('name', $allowedRoles)->get();

        if ($roles->isEmpty()) {
            $this->command->error('Tidak ada Role terkait yang ditemukan di database.');
            return;
        }

        $apiPermissions = [
            ['table' => 'item_requests', 'method' => 'index', 'name' => 'View item requests (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'show', 'name' => 'View single item request (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'store', 'name' => 'Create item request (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'update', 'name' => 'Update item request (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'destroy', 'name' => 'Delete item request (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'workflowApi', 'name' => 'View workflow item request (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'addVendor', 'name' => 'Add vendor (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'delivery', 'name' => 'Delivery item request (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'getDelivery', 'name' => 'View delivery detail (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'loadByCompany', 'name' => 'Load item request by company (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'categories', 'name' => 'Get item request categories (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'types', 'name' => 'Get item request type (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'sprinters', 'name' => 'Get item request sprinters (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'productSuppliers', 'name' => 'Get item request suppliers (Mobile)', 'guard_name' => 'api'],

            ['table' => 'item_purchases', 'method' => 'store', 'name' => 'Create item purchase (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_purchases', 'method' => 'update', 'name' => 'Update item purchase (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_purchases', 'method' => 'payment', 'name' => 'Payment item purchase (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_purchases', 'method' => 'getPayment', 'name' => 'Get Payment detail (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_purchases', 'method' => 'closed', 'name' => 'Close item request (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_purchases', 'method' => 'complete', 'name' => 'Complete item request (Mobile)', 'guard_name' => 'api'],
        ];

        DB::beginTransaction();
        try {
            foreach ($apiPermissions as $permData) {
                $permission = Permission::firstOrCreate([
                    'table' => $permData['table'],
                    'method' => $permData['method'],
                    'guard_name' => $permData['guard_name']
                ],[
                    'name' => $permData['name'],
                    'model' => 'Mobile', 
                ]);

                foreach ($roles as $role) {
                    $exists = PermissionRole::where('role_id', $role->id)
                                            ->where('permission_id', $permission->id)
                                            ->exists();
                    
                    if (!$exists) {
                        PermissionRole::create([
                            'role_id' => $role->id, 
                            'permission_id' => $permission->id
                        ]);
                    }
                }
            }
            
            DB::commit();
            $this->command->info('Semua Permission API untuk Item Request & Purchase berhasil ditambahkan ke role yang diizinkan.');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Seeder PermissionForItemRequestApiSeeder Error: ' . $th->getMessage());
            $this->command->error('Gagal menambahkan permission. Cek log untuk detailnya.');
        }
    }
}