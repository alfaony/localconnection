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
            $company->name = "Internet Rakyat";
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

