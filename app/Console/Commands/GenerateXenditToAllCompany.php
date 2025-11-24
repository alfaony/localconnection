<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SettingCompany;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Schemas\RoleSchema;

class GenerateXenditToAllCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:setting-xendit-to-all-company';

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
        $menu = 'xendit_internet_customer';
        $fields = ['public_key' => '', 'secret_key' => '', 'webhook_token' => '','environment'=>''];
        
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

            foreach ($fields as $key => $value) 
            {
                $fieldExists = SettingCompany::byCompany($company->id)->where('field_title', $key)->first();
                
                if (!$fieldExists) 
                {
                    // Jika field belum ada, buat baru
                    $field = new SettingCompany();
                    $field->menu = $menu;
                    $field->user_id = $user->id;
                    $field->field_title = $key;
                    $field->field_value = $value;
                    $field->save();

                    $checking = "create";
                } 
                else 
                {
                    // Jika field sudah ada, lakukan update
                    $fieldExists->field_value = $value;
                    $fieldExists->save();

                    $checking = "update";
                    
                }
            }
            $this->info("Successfully {$checking} for company '{$company->name}'.");
        }
        return Command::SUCCESS;
    }
}

