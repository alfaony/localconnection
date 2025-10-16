<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TemplateAgreement;

class MappingAgreementTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // all Bos
        TemplateAgreement::firstOrCreate(
            ['template_agreement' => 'templateBos1'], // Kriteria pencarian
            [
                'template_name' => 'templateBos1',
                'template_agreement_show' => 'Template Bos 1',
                'is_active' => true,
                'is_default' => true,
            ]
        );

        TemplateAgreement::firstOrCreate(
            ['template_agreement' => 'templateBos1_1'], // Kriteria pencarian
            [
                'template_name' => 'templateBos1',
                'template_agreement_show' => 'SEWA STUDIO LIVE COMMERCE',
                'is_active' => true,
                'is_default' => false,
            ]
        );

        TemplateAgreement::firstOrCreate(
            ['template_agreement' => 'templateBos1_2'], // Kriteria pencarian
            [
                'template_name' => 'templateBos1',
                'template_agreement_show' => 'PENYEWAAN LAPTOP',
                'is_active' => true,
                'is_default' => false,
            ]
        );

        // Bost 3
        TemplateAgreement::firstOrCreate(
            ['template_agreement' => 'templateBos3_1'], // Kriteria pencarian
            [
                'template_name' => 'templateBos3',
                'template_agreement_show' => 'Template 1 Sewa Kosan',
                'is_active' => true,
                'is_default' => false,
            ]
        );


        TemplateAgreement::firstOrCreate(
            ['template_agreement' => 'templateBos3_2'], // Kriteria pencarian
            [
                'template_name' => 'templateBos3',
                'template_agreement_show' => 'Template 2 Sewa Unit',
                'is_active' => true,
                'is_default' => false,
            ]
        );


        TemplateAgreement::firstOrCreate(
            ['template_agreement' => 'templateBos3'], // Kriteria pencarian
            [
                'template_name' => 'templateBos3',
                'template_agreement_show' => 'Template 3',
                'is_active' => true,
                'is_default' => true,
            ]
        );
    }
}