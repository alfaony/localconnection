<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;

class GenerateMasterTypePerCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $components = [
            'Monitor',
            'Keyboard',
            'Battery',
            'Camera',
            'Charger',
            'Mouse Pad',
            'Body',
            'Speaker',
            'Wifi',
        ];

        $companies = Company::all();
        foreach ($companies as $value) 
        {
            foreach ($components as $component) {
                $masterCheckItem = \App\Models\MasterCheckItem::where('company_id', $value->id)->where('name', $component)->first();
                if (!$masterCheckItem) {
                    \App\Models\MasterCheckItem::create([
                        'company_id' => $value->id,
                        'name' => $component,
                    ]);
                }
            }
        }
        
        echo 'Done';
    }
}
