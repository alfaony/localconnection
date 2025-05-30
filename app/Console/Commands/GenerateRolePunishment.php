<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SettingCompany;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Schemas\RoleSchema;

class GenerateRolePunishment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:setting-punishment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate or update asset head letters for all companies.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $adminRole = Role::where('name', RoleSchema::ADMIN)->first();
        $rootRole = Role::where('name', RoleSchema::ROOT)->first();
        
        // Validasi jika role tidak ditemukan
        if (!$adminRole || !$rootRole) {
            $this->error("Admin or Root role not found.");
            return Command::FAILURE;
        }
        
        $companies = Company::all();
        $menuPunishmentTaskDoing = 'punishment_task_doing';
        $fieldPunishmentTaskDoing = ['status_punihsment_task_doing' => 1, 'point_punishment_task_doing' => -10];

        $menuPunishment = 'punishment';
        $fieldPunishment = ['point_punishment_task_todo' => -10, 'point_punishment_weekly_report' => -100];
        
        foreach ($companies as $company) 
        {
            // Mencari user dengan role admin atau root pada setiap perusahaan
            $user = User::where('company_id', $company->id)
                ->where(function ($query) use ($rootRole, $adminRole) {
                    $query->where('role_id', $rootRole->id)
                          ->orWhere('role_id', $adminRole->id);
                })
                ->first();

            if (!$user) {
                $this->error("No Admin or Root user found for company {$company->name}.");
                continue;
            }
            $checking = "";

            foreach ($fieldPunishmentTaskDoing as $key => $value) 
            {
                $fieldExists = SettingCompany::byCompany($company->id)->where('field_title', $key)->first();
                
                if (!$fieldExists) 
                {
                    // Jika field belum ada, buat baru
                    $field = new SettingCompany();
                    $field->menu = $menuPunishmentTaskDoing;
                    $field->user_id = $user->id;
                    $field->field_title = $key;
                    $field->field_value = $value;
                    $field->save();

                    $checking = "create";
                } 
            }

            foreach ($fieldPunishment as $key2 => $value2) 
            {
                $fieldExists = SettingCompany::byCompany($company->id)->where('field_title', $key2)->first();
                
                if (!$fieldExists) 
                {
                    // Jika field belum ada, buat baru
                    $field = new SettingCompany();
                    $field->menu = $menuPunishment;
                    $field->user_id = $user->id;
                    $field->field_title = $key2;
                    $field->field_value = $value2;
                    $field->save();

                    $checking = "create";
                } 
            }

            $this->info("Successfully for company '{$company->name}'.");
        }
        return Command::SUCCESS;
    }
}

