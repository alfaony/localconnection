<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SettingCompany;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\AssetType;

class UpdateAssetType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:asset-type';

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
        $assetKeys = AssetType::where('name',"kunci")->get();
        foreach ($assetKeys as $asset) 
        {
            $asset->name = "Kunci Lain";
            $asset->save();
        }

        $assetKeys = AssetType::where('name',"Laptop")->get();
        foreach ($assetKeys as $asset) 
        {
            $asset->delete();
        }

        
    
    }
}

