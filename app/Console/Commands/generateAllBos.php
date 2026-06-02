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

class generateAllBos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:allbos';

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
        DB::beginTransaction();
        try {
            //code...
            
            $this->info('Generating default bos 1...');

            // BOS 1
            $company = new Company();
            $company->name = "BOS 1";
            $company->save();

            $user = User::all();
            foreach ($user as $a) 
            {
                if(!$a->company_id)
                {
                    $a->company_id = $company->id;
                    $a->save();
                }
            }


            // $this->info('Generating default bos 2...');

            // // BOS 2
            // $companyBos2 = new Company();
            // $companyBos2->name = "BOS 2";
            // $companyBos2->save();

            // $role = Role::where('name',RoleSchema::ADMIN)->first();

            // $userBos2 = new User();
            // $userBos2->name = "ADMIN BOS 2";
            // $userBos2->email = "bos2@emcdev.me";
            // $userBos2->role_id = $role->id;
            // $userBos2->password = bcrypt("root123!");
            // $userBos2->company_id = $companyBos2->id;
            
            // $userBos2->save();

            // $field = ['name' => 'PT. Aidia Media Semesta','director'=>'Dr. Eddy Yansen','address' => 'GSA #B8/DH. Jl.S.Parman. Jakarta Barat','npwp_number' => '0707979797','currency'=>'Rp','currency_usd'=>"15000",'nib_file'=>'','acta_file'=> '','npwp_file' => '','signature_file' => ''];

            // foreach ($field as $key => $value) 
            // {
            //     $field = new SettingCompany();
            //     $field->user_id = $userBos2->id;
            //     $field->field_title = $key;
            //     $field->field_value = $value;
            //     $field->save();        
            // }

            // // copy product
            // $product = Product::byCompany($company->id)->get();
            // foreach ($product as $a) 
            // {
            //     $productCopy = new Product();
            //     $productCopy->name = $a->name;
            //     $productCopy->price_buy = $a->price_buy;
            //     $productCopy->price_sell = $a->price_sell;
            //     $productCopy->method_count = $a->method_count;
            //     $productCopy->user_created_id = $userBos2->id;
            //     $productCopy->user_updated_id = $userBos2->id;
            //     $productCopy->save();
            // }

            // $this->info('Generating default bos 3...');
            // // BOS 3
            // $companyBos3 = new Company();
            // $companyBos3->name = "BOS 3";
            // $companyBos3->save();

            // $role = Role::where('name',RoleSchema::ADMIN)->first();

            // $userBos3 = new User();
            // $userBos3->name = "ADMIN BOS 3";
            // $userBos3->email = "bos3@emcdev.me";
            // $userBos3->role_id = $role->id;
            // $userBos3->password = bcrypt("root123!");
            // $userBos3->company_id = $companyBos3->id;
            
            // $userBos3->save();

            // $field = ['name' => 'CV. Office Plus','director'=>'Dr. Eddy Yansen','address' => 'GSA #B8/DH. Jl.S.Parman. Jakarta Barat','npwp_number' => '0707979797','currency'=>'Rp','currency_usd'=>"15000",'nib_file'=>'','acta_file'=> '','npwp_file' => '','signature_file' => ''];

            // foreach ($field as $key => $value) 
            // {
            //     $field = new SettingCompany();
            //     $field->user_id = $userBos3->id;
            //     $field->field_title = $key;
            //     $field->field_value = $value;
            //     $field->save();        
            // }

            // $this->info('Generating default bos 4...');
            // // BOS 4
            // $companyBos4 = new Company();
            // $companyBos4->name = "BOS 4";
            // $companyBos4->save();

            // $role = Role::where('name',RoleSchema::ADMIN)->first();

            // $userBos4 = new User();
            // $userBos4->name = "ADMIN BOS 4";
            // $userBos4->email = "bos4@emcdev.me";
            // $userBos4->role_id = $role->id;
            // $userBos4->password = bcrypt("root123!");
            // $userBos4->company_id = $companyBos4->id;
            
            // $userBos4->save();

            // $field = ['name' => 'PT. Suryadhamma Investama','director'=>'Dr. Eddy Yansen','address' => 'GSA #B8/DH. Jl.S.Parman. Jakarta Barat','npwp_number' => '0707979797','currency'=>'Rp','currency_usd'=>"15000",'nib_file'=>'','acta_file'=> '','npwp_file' => '','signature_file' => ''];

            // foreach ($field as $key => $value) 
            // {
            //     $field = new SettingCompany();
            //     $field->user_id = $userBos3->id;
            //     $field->field_title = $key;
            //     $field->field_value = $value;
            //     $field->save();        
            // }

            DB::commit();
            return Command::SUCCESS;
        } catch (\Throwable $th) 
        {
            //throw $th;
            DB::rollback();
            // dd($th);
            Log::error($th);
            return $th;
        }
    }
}

