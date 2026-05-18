<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PartnershipAgreementType;

class PartnershipAgreementTypeSeeder extends Seeder
{
    public function run()
    {
        $agreementTypes = [
            ['name' => 'Perjanjian Berlangganan Internet', 'name_format' => 'perjanjian_berlangganan_internet', 'count_signature' => 1],
            ['name' => 'Perjanjian Freelance', 'name_format' => 'perjanjian_freelance', 'count_signature' => 2],
            ['name' => 'Perjanjian Kerjasama Kemitraan', 'name_format' => 'kerjasama_kemitraan', 'count_signature' => 2],
            ['name' => 'Perjanjian Konsinyasi (Titip Jual)', 'name_format' => 'konsinyasi_titip_jual', 'count_signature' => 2],
            ['name' => 'Perjanjian Table Ads (Pemilik Restoran)', 'name_format' => 'table_ads_pemilik_restoran', 'count_signature' => 2],
            ['name' => 'Perjanjian Table Ads (Pengiklan)', 'name_format' => 'perjanjian_table_ads_pengiklan', 'count_signature' => 2],
            ['name' => 'Perjanjian untuk KOL', 'name_format' => 'perjanjian_untuk_kol', 'count_signature' => 2],
            ['name' => 'Perjanjian Kerjasama HikariBiz', 'name_format' => 'perjanjian_kerjasama_hikari_biz', 'count_signature' => 2],
            ['name' => 'NDA - Jasa Pembukuan', 'name_format' => 'nda_jasa_pembukuan', 'count_signature' => 2],
        ];

        foreach ($agreementTypes as $type) {
            PartnershipAgreementType::updateOrCreate([
                'name' => $type['name'],
            ], [
                'name_format' => $type['name_format'],
                'count_signature' => $type['count_signature']
            ]);
        }
    }
}