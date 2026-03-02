<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\Role;
use App\Helpers\Access;

class ClearPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            $roles = Role::all();

            foreach ($roles as $role) {
                $roleId = $role->id;
                Access::clearCacheForRole($roleId);
                Cache::forget("role_permissions:{$roleId}");

                $this->command->info("✅ Cache role_permissions untuk {$role->name} role berhasil dihapus.");
            }


    }
}
