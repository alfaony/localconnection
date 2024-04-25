<?php

namespace Database\Seeders;

use App\Schemas\RoleSchema;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SettingCompany;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\AssetType;


class AssetTypeForAllCompanySeeder extends Seeder
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
        $fields = ['kunci','Kartu Akses','Laptop'];
        
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
                $assets = AssetType::byCompany($a->id)->where('name',$value)->first();
                if(!$assets)
                {
                    $asset = new AssetType();
                    $asset->name = $value;
                    $asset->user_id = $user->id;
                    $asset->save();
                }else
                {
                    $assets->name = $value;
                    $assets->save();
                }
            }
        }

    }
}


