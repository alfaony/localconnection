<?php

namespace Database\Seeders;

use App\Schemas\RoleSchema;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SettingCompany;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;


class AttendanceSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = Role::where('name',RoleSchema::ADMIN)->first();
        $root = Role::where('name',RoleSchema::ROOT)->first();
        $company = Company::all();
        $menu = 'email';
        $fields = ['clock_in' => '08:00','reward_point_conversion' => '500','late_point'=>-10,'on_time_poin'=>0];
        
        foreach ($company as $a) 
        {

            $user = User::where('company_id', $a->id)
                ->where(function ($query) use ($root, $admin) {
                    $query->where('role_id', $root->id)
                        ->orWhere('role_id', $admin->id);
                })
                ->first();


            foreach ($fields as $key => $value) 
            {
                $fieldExists = SettingCompany::byCompany($a->id)->where('field_title',$key)->first();
                if(!$fieldExists)
                {
                    $field = new SettingCompany();
                    $field->menu = $menu;
                    $field->user_id = $user->id;
                    $field->field_title = $key;
                    $field->field_value = $value;
                    $field->save();
                }else
                {
                    $fieldExists->field_title = $key;
                    $fieldExists->field_value = $value;
                    $fieldExists->save();
                }
            }
        }

    }
}

