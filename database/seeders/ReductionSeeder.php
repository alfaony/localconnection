<?php
namespace Database\Seeders;

use App\Schemas\RoleSchema;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use App\Models\Reduction;


class ReductionSeeder extends Seeder
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
        
        foreach ($company as $a) 
        {

            $user = User::where('company_id', $a->id)
                ->where(function ($query) use ($root, $admin) {
                    $query->where('role_id', $root->id)
                        ->orWhere('role_id', $admin->id);
                })
                ->first();

            $reduction = new Reduction();
            $reduction->user_id = $user->id;
            $reduction->name ="Hilang";
            $reduction->save();

            $reduction = new Reduction();
            $reduction->user_id = $user->id;
            $reduction->name ="Terpakai";
            $reduction->save();

            $reduction = new Reduction();
            $reduction->user_id = $user->id;
            $reduction->name ="Rusak";
            $reduction->save();
        }

    }
}
