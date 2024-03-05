<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Schemas\RoleSchema;

use App\Models\Company;
use App\Models\User;
use App\Models\Product;
use App\Models\Role;
use App\Models\SettingCompany;

class CreateKeloolaCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:companykeloola';

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
        // BOS 2
        $companyKeloola = new Company();
        $companyKeloola->name = "Keloola";
        $companyKeloola->save();

        $role = Role::where('name',RoleSchema::ADMIN)->first();

        $keloola = new User();
        $keloola->name = "ADMIN Keloola";
        $keloola->email = "keloola@emcdev.me";
        $keloola->role_id = $role->id;
        $keloola->password = bcrypt("root123!");
        $keloola->company_id = $companyKeloola->id;
        
        $keloola->save();

        $field = ['name' => 'Keloola','director'=>'Dr. Eddy Yansen','address' => 'GSA #B8/DH. Jl.S.Parman. Jakarta Barat','npwp_number' => '0707979797','currency'=>'Rp','currency_usd'=>"15000",'nib_file'=>'','acta_file'=> '','npwp_file' => '','signature_file' => ''];

        foreach ($field as $key => $value) 
        {
            $field = new SettingCompany();
            $field->user_id = $keloola->id;
            $field->field_title = $key;
            $field->field_value = $value;
            $field->save();        
        }

        $menu = 'email';
        $fields = ['host' => '','port' => '','username' => '','password' => '','encryption'=> '','sent_time'=>'','sent_time_status'=>''];
        

        foreach ($fields as $key => $value) 
        {
            $fieldExists = SettingCompany::byCompany($companyKeloola->id)->where('field_title',$key)->first();
            if(!$fieldExists)
            {
                $field = new SettingCompany();
                $field->menu = $menu;
                $field->user_id = $keloola->id;
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
        return Command::SUCCESS;
    }
}
