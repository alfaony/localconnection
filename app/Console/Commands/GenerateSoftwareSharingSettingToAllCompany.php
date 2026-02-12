<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SettingCompany;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Schemas\RoleSchema;

class GenerateSoftwareSharingSettingToAllCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:software-sharing-to-all-company';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate internet invoice branding settings for all companies.';

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
        $menu = 'software_sharing_setting';
        $fields = ['software_sharing_icon' => '','software_sharing_company_name' => '','software_sharing_company_address' => ''
        ,'software_sharing_phone' => '','software_sharing_footer_message' => ''
        , 'software_sharing_message_blast' => ''
        , 'software_sharing_manual_payment_status' => ''
        , 'nama_bank_software_sharing','atas_nama_software_sharing','cabang_bank_software_sharing','rekening_number_software_sharing'
    ];
        
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
            }

            $this->info("Successfully {$checking} for company '{$company->name}'.");
        }
        return Command::SUCCESS;
    }
}



