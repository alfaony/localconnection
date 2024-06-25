<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Models\Asset;
use App\Models\AssetType;
use App\Schemas\RoleSchema;
use App\Models\EquipmentReduction;
use App\Models\Reduction;

class MoveObFromBos1ToBos3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moveob:bos1tobos3';

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

        $bos1 = Company::where('name','like','%BOS 1%')->first();
        $bos3 = Company::where('name','like','%BOS 3%')->first();

        $bm = Role::where('name',RoleSchema::BM)->first();
        $ob = Role::where('name',RoleSchema::OB)->first();

        // user Migration OB
        $userBM = User::where('company_id',$bos1->id)->where('role_id',$bm->id)->get();
        $userOb = User::where('company_id',$bos1->id)->where('role_id',$ob->id)->get();

        DB::beginTransaction();
        try {
            //code...
            if($bos1 && $bos3)
            {
                foreach($userBM as $user){
                    $user->company_id = $bos3->id;
                    $user->save();
                }
    
                foreach($userOb as $user)
                {
                    $user->company_id = $bos3->id;
                    $user->save();
                }
    
                // Asset Migration
                $asset = Asset::byCompany($bos1->id)->get();        
                if ($asset->isEmpty()) 
                {
                    $this->info('No assets found for BOS 1');
                } else {
                    foreach($asset as $a)
                    {
                    $name = $a->assetType->name;
                    $assetTypeNew = AssetType::where('name',$name)->byCompany($bos3->id)->first();
    
                    $a->asset_type_id = $assetTypeNew->id;
                    $a->save();
                    }
                }
    
                $equipmentReduction = EquipmentReduction::byCompany($bos1->id)->get();
                if ($equipmentReduction->isEmpty()) 
                {
                    $this->info('No equipment reduction found for BOS 1');
                } else 
                {
                    foreach($equipmentReduction as $a )
                    {
                        $reductionName = $a->reduction->name;
                        $reductionNew = Reduction::where('name',$reductionName)->byCompany($bos3->id)->first();
                        $a->reduction_id = $reductionNew->id;
                        $a->save();
                    }
                }
    
    
                DB::commit();
                $this->info('Migration Success BOS 1 KE BOS 3 OB');
            }else
            {
                $this->info('Company not found');
            }

            return Command::SUCCESS;
        } catch (\Throwable $th) {
            //throw $th;

            DB::rollBack();
            $this->info($th->getMessage());
        }
    }
}