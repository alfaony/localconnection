<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Schemas\RoleSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForKyeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ClearPermissionSeeder::class);

        $methods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'download', 'export', 'approvement','verifyemail','KyeExport'];

        // Hanya ROOT dan ADMIN untuk metode tertentu
        $restrictedRoles = [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::FINANCE];
        $roles = Role::all();

        foreach ($methods as $method) {
            // Buat izin
            $permission = Permission::firstOrCreate([
                'name' => ucwords($method) . ' KYE ( Know YOur Employee )',
            ], [
                'method' => $method,
                'table' => 'kyes',
                'model' => 'Kye',
                'guard_name' => 'web'
            ]);

            // Assign role & permission
            foreach ($roles as $role) {
                // Hanya ROOT dan ADMIN untuk metode tertentu
                if (in_array($method, ['index', 'approvement', 'destroy','KyeExport'])) {
                    if (in_array($role->name, $restrictedRoles)) {
                        PermissionRole::firstOrCreate(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }
                } else {
                    // Semua role lainnya mendapatkan izin untuk metode lain
                    PermissionRole::firstOrCreate(['role_id' => $role->id, 'permission_id' => $permission->id]);
                }
            }
        }

        $this->command->info('✅ Izin untuk Kye berhasil ditambahkan.');
    }

}









