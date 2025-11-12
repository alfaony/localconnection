<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
use App\Models\Permission; 
use App\Models\PermissionRole; 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobilePermissionSeeder extends Seeder
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
            ['table' => 'tasks', 'method' => 'index', 'name' => 'View all tasks (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'show', 'name' => 'View single task (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'store', 'name' => 'Create task (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'update', 'name' => 'Update task (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'destroy', 'name' => 'Delete task (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'statusChange', 'name' => 'Change task status (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'indexToday', 'name' => 'View tasks today (Mobile)', 'guard_name' => 'api'],
            ['table' => 'homes', 'method' => 'indexSummary', 'name' => 'View home summary (Mobile)', 'guard_name' => 'api'],
            ['table' => 'task_statuses', 'method' => 'indexTaskStatuses', 'name' => 'View Task Statuses (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_projects', 'method' => 'indexDailyTaskProjects', 'name' => 'View Task Projects (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_projects_titles', 'method' => 'indexProjects', 'name' => 'View Task Projects Title (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_categories', 'method' => 'indexDailyTaskCategories', 'name' => 'View Task Categories (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_types', 'method' => 'indexDailyTaskTypes', 'name' => 'View Task Types (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_objectives', 'method' => 'indexDailyTaskObjectives', 'name' => 'View Task Objectives (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_keyresults', 'method' => 'indexKeyResults', 'name' => 'View Task Key Results (Mobile)', 'guard_name' => 'api'],
            ['table' => 'daily_task_users', 'method' => 'indexDailyTaskUsers', 'name' => 'View Task Users (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'report', 'name' => 'Create task report (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'approval', 'name' => 'Approve task (Mobile)', 'guard_name' => 'api'],
            ['table' => 'medias', 'method' => 'generateMediaUrl', 'name' => 'Generate Signed Media URL (Mobile)', 'guard_name' => 'api'],
            ['table' => 'divisions', 'method' => 'checkDivisionQuota', 'name' => 'Check Division Quota (Mobile)', 'guard_name' => 'api'],
            ['table' => 'divisions', 'method' => 'indexDivision', 'name' => 'View Divisions (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'indexTomorrow', 'name' => 'View tomorrow tasks (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'indexOverdue', 'name' => 'View overdue tasks (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'updateMedia', 'name' => 'Update Task Media (Mobile)', 'guard_name' => 'api'],
            ['table' => 'tasks', 'method' => 'deleteMedia', 'name' => 'Delete Task Media (Mobile)', 'guard_name' => 'api'],

            ['table' => 'item_requests', 'method' => 'index', 'name' => 'View item requests (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'show', 'name' => 'View single item request (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'store', 'name' => 'Create item request (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'update', 'name' => 'Update item request (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'destroy', 'name' => 'Delete item request (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'workflowApi', 'name' => 'View workflow item request (Mobile)', 'guard_name' => 'api'],

            ['table' => 'item_requests', 'method' => 'addVendor', 'name' => 'Add vendor (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_requests', 'method' => 'delivery', 'name' => 'Delivery item request (Mobile)', 'guard_name' => 'api'],

            ['table' => 'item_purchases', 'method' => 'store', 'name' => 'Create item purchase (Mobile)', 'guard_name' => 'api'],
            ['table' => 'item_purchases', 'method' => 'update', 'name' => 'Update item purchase (Mobile)', 'guard_name' => 'api'],

            ['table' => 'item_purchases', 'method' => 'payment', 'name' => 'Payment item purchase (Mobile)', 'guard_name' => 'api'],
            



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
        
        $this->command->info('Semua Permission mobile berhasil ditambahkan');
    }
}