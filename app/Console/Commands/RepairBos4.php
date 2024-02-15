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

class RepairBos4 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:bos4';

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
            // Destroy Setting BOS 3
            $userBos3 = User::where('email','bos3@emcdev.me')->first();
            $SettingCompany = SettingCompany::where('user_id',$userBos3->id)->delete();


            // BOS 3
            $field = ['name' => 'CV. OFFICEPLUS','director'=>'Dr. Eddy Yansen','address' => 'GSA #B8/DH. Jl.S.Parman. Jakarta Barat','npwp_number' => '0707979797','currency'=>'Rp','currency_usd'=>"15000",'nib_file'=>'','acta_file'=> '','npwp_file' => '','signature_file' => '','template_perjanjian'=>'templateBos3'];

            foreach ($field as $key => $value) 
            {
                $field = new SettingCompany();
                $field->user_id = $userBos3->id;
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();        
            }

            $this->info('Generating default bos 4...');
            // BOS 4
            $userBos4 = User::where('email','bos4@emcdev.me')->first();
            $SettingCompany = SettingCompany::where('user_id',$userBos3->id);

            $field = ['name' => 'PT. Suryadhamma Investama','director'=>'Dr. Eddy Yansen','address' => 'GSA #B8/DH. Jl.S.Parman. Jakarta Barat','npwp_number' => '0707979797','currency'=>'Rp','currency_usd'=>"15000",'nib_file'=>'','acta_file'=> '','npwp_file' => '','signature_file' => '','template_perjanjian'=>'templateBos1'];

            foreach ($field as $key => $value) 
            {
                $field = new SettingCompany();
                $field->user_id = $userBos4->id;
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();        
            }

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


