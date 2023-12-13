<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Schemas\RoleSchema;

use App\Models\Company;
use App\Models\User;
use App\Models\Role;
use App\Models\SettingCompany;

class GenerateAgreementTamplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:agreementtamplate';

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
        $company = Company::get();
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $admin = Role::where('name',RoleSchema::ADMIN)->first();

        $tamplatePerjanjian = ['templateBos1','templateBos3'];
        foreach ($company as $a) 
        {
            $user =$a->user()->first();
            if($a->name == "BOS 3")
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->field_title = "template_perjanjian";
                $field->field_value = $tamplatePerjanjian[1];
                $field->save();
            }else
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->field_title = "template_perjanjian";
                $field->field_value = $tamplatePerjanjian[0];
                $field->save();
            }
        }

        $this->info("Berhasil Eksekusi Kode");

    }
}
