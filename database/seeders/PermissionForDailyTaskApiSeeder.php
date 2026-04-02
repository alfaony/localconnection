<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
use App\Models\Permission; 
use App\Models\PermissionRole; 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionForDailyTaskApiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ClearPermissionSeeder::class);
        
        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->command->error('Tidak ada Role ditemukan di database.');
            return;
        }

        $mobilePermissions = [
            ['table' => 'tasks', 'method' => 'index', 'name' => 'View all tasks (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'show', 'name' => 'View single task (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'store', 'name' => 'Create task (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'update', 'name' => 'Update task (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'destroy', 'name' => 'Delete task (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'statusChange', 'name' => 'Change task status (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'indexToday', 'name' => 'View tasks today (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'report', 'name' => 'Create task report (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'approval', 'name' => 'Approve task (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'indexTomorrow', 'name' => 'View tomorrow tasks (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'indexOverdue', 'name' => 'View overdue tasks (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'updateMedia', 'name' => 'Update Task Media (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'deleteMedia', 'name' => 'Delete Task Media (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'indexTaskByDivision', 'name' => 'View tasks by division (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'indexTaskByUser', 'name' => 'View daily tasks by specific user (Mobile)', 'guard_name' => 'api'],
            
            ['table' => 'daily_task_projects', 'method' => 'indexDailyTaskProjects', 'name' => 'View Task Projects (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_projects', 'method' => 'indexProjects', 'name' => 'View Task Projects Title (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_projects', 'method' => 'indexDailyTaskCategories', 'name' => 'View Task Categories (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_projects', 'method' => 'indexDailyTaskTypes', 'name' => 'View Task Types (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_projects', 'method' => 'indexDailyTaskObjectives', 'name' => 'View Task Objectives (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_projects', 'method' => 'indexKeyResults', 'name' => 'View Task Key Results (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_projects', 'method' => 'indexDailyTaskUsers', 'name' => 'View Task Users (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_projects', 'method' => 'indexTaskStatuses', 'name' => 'View Task Statuses (Mobile)', 'guard_name' => 'api'],

            ['table' => 'dailytasks', 'method' => 'indexSummary', 'name' => 'View home summary (Mobile)', 'guard_name' => 'api'],
            ['table' => 'dailytasks', 'method' => 'generateMediaUrl', 'name' => 'Generate Signed Media URL (Mobile)', 'guard_name' => 'api'],
            ['table' => 'dailytasks', 'method' => 'checkDivisionQuota', 'name' => 'Check Division Quota (Mobile)', 'guard_name' => 'api'],
            ['table' => 'dailytasks', 'method' => 'indexDivision', 'name' => 'View Divisions (Mobile)', 'guard_name' => 'api'],
            ['table' => 'dailytasks', 'method' => 'getUsersByDivision', 'name' => 'View users by division (Mobile)', 'guard_name' => 'api'],
        ];

        foreach ($mobilePermissions as $permData) 
        {
            $permission = Permission::firstOrCreate([
                'table' => $permData['table'],
                'method' => $permData['method'],
            ],[
                'name' => $permData['name'],
                'model' => 'Mobile', 
                'guard_name' => $permData['guard_name'] 
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
        
        $this->command->info('Semua Permission berhasil ditambahkan');
    }
}