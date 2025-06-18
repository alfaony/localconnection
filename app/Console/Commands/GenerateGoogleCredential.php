<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SettingCompany;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Schemas\RoleSchema;

class GenerateGoogleCredential extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:credentail-google';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $menu = 'google';

        $fields = ['google_client_id' => null,'google_client_secret' => null, 'google_redirect_uri' => null, 'google_refresh_token' => null, 'google_access_token' => null,'google_expires_at' => null , 'google_token_created_at' => null];
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
                } 
                $this->info("Successfully for company '{$company->name}'.");
            }

            $fieldClosedTimeExists = SettingCompany::byCompany($company->id)->where('field_title', "closed_time")->first();
            if (!$fieldClosedTimeExists) 
            {
                // Jika field belum ada, buat baru
                $field = new SettingCompany();
                $field->menu = "profile";
                $field->user_id = $user->id;
                $field->field_title = "closed_time";
                $field->field_value = "16:00";
                $field->save();
            }

        }
        return Command::SUCCESS;
    }
}


