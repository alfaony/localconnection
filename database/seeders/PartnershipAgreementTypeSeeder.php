<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PartnershipAgreementType;

class PartnershipAgreementTypeSeeder extends Seeder
{
    public function run()
    {
        $agreementTypes = [
            ['name' => 'Perjanjian Berlangganan Internet', 'name_format' => 'perjanjian_berlangganan_internet'],
            ['name' => 'Perjanjian Freelance', 'name_format' => 'perjanjian_freelance'],
            ['name' => 'Perjanjian Kerjasama Kemitraan', 'name_format' => 'kerjasama_kemitraan'],
            ['name' => 'Perjanjian Konsinyasi (Titip Jual)', 'name_format' => 'konsinyasi_titip_jual'],
            ['name' => 'Perjanjian Table Ads (Pemilik Restoran)', 'name_format' => 'table_ads_pemilik_restoran'],
            ['name' => 'Perjanjian Table Ads (Pengiklan)', 'name_format' => 'perjanjian_table_ads_pengiklan'],
            ['name' => 'Perjanjian untuk KOL', 'name_format' => 'perjanjian_untuk_kol'],
        ];

        foreach ($agreementTypes as $type) {
            PartnershipAgreementType::firstOrCreate([
                'name' => $type['name'],
                'name_format' => $type['name_format']
            ]);
        }
    }
}