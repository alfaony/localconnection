<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SettingCompany;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Schemas\RoleSchema;

class GenerateAllAssetHeadLetterAllCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:asset_head_letter_all_company';

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
        $menu = 'asset_head_letter';
        $fields = ['header' => '', 'footer' => ''];
        
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

                    $this->info("Successfully created field '{$key}' for company '{$company->name}'.");
                } 
                else 
                {
                    // Jika field sudah ada, lakukan update
                    $fieldExists->field_value = $value;
                    $fieldExists->save();

                    $this->info("Successfully updated field '{$key}' for company '{$company->name}'.");
                }
            }
        }
        return Command::SUCCESS;
    }
}