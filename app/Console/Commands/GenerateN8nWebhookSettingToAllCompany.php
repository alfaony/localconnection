<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SettingCompany;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Schemas\RoleSchema;

class GenerateN8nWebhookSettingToAllCompany extends Command
{
    protected $signature = 'generate:setting-n8n-to-all-company';

    protected $description = 'Generate n8n webhook token setting for all companies.';

    public function handle()
    {
        $adminRole = Role::where('name', RoleSchema::ADMIN)->first();
        $rootRole = Role::where('name', RoleSchema::ROOT)->first();

        if (!$adminRole || !$rootRole) {
            $this->error("Admin or Root role not found.");
            return Command::FAILURE;
        }

        $companies = Company::all();
        $menu = 'n8n';
        $fieldTitle = 'n8n_webhook_token';

        foreach ($companies as $company) {

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

            $fieldExists = SettingCompany::byCompany($company->id)
                ->where('field_title', $fieldTitle)
                ->first();

            if (!$fieldExists) {

                $field = new SettingCompany();
                $field->menu = $menu;
                $field->user_id = $user->id;
                $field->field_title = $fieldTitle;
                $field->field_value = '';
                $field->save();

                $this->info("Created n8n setting for company '{$company->name}'.");
            } else {
                $this->warn("Setting already exists for company '{$company->name}'.");
            }
        }

        return Command::SUCCESS;
    }
}
