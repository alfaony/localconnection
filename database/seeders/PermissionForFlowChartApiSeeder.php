<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
use App\Models\Permission; 
use App\Models\PermissionRole; 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForFlowChartApiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->command->error('Tidak ada Role ditemukan di database.');
            return;
        }

        $mobilePermissions = [
            ['method' => 'index', 'name' => 'View all flowchart (Mobile)'],
            ['method' => 'show', 'name' => 'View single flowchart (Mobile)'],
            ['method' => 'store', 'name' => 'Create flowchart (Mobile)'],
            ['method' => 'update', 'name' => 'Update flowchart (Mobile)'],
            ['method' => 'destroy', 'name' => 'Delete flowchart (Mobile)'],


        ];

        foreach ($mobilePermissions as $permData) 
        {
            $permission = Permission::firstOrCreate([
                'table' => 'flow_charts',
                'method' => $permData['method'],
            ],[
                'name' => $permData['name'],
                'model' => 'Mobile', 
                'guard_name' => 'api'
            ]);

            foreach ($roles as $role) 
            {
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
        
        $this->command->info('Semua Permission Api berhasil ditambahkan');
    }
}