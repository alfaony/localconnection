<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TemplateAgreement;

class MappingAgreementTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mapping:agreement-templates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed mapping agreement templates into the database';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Seeding Mapping Agreement Templates...');

        $templates = [
            [
                'template_agreement' => 'templateBos1',
                'template_name' => 'templateBos1',
                'template_agreement_show' => 'Template Bos 1',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'template_agreement' => 'templateBos3_1',
                'template_name' => 'templateBos3',
                'template_agreement_show' => 'Template 1 Sewa Kosan',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'template_agreement' => 'templateBos3_2',
                'template_name' => 'templateBos3',
                'template_agreement_show' => 'Template 2 Sewa Unit',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'template_agreement' => 'templateBos3',
                'template_name' => 'templateBos3',
                'template_agreement_show' => 'Template 3',
                'is_active' => true,
                'is_default' => true,
            ],
        ];

        foreach ($templates as $template) {
            TemplateAgreement::firstOrCreate(
                ['template_agreement' => $template['template_agreement']],
                $template
            );
            $this->info("Seeded: {$template['template_agreement']}");
        }

        $this->info('Seeding Completed Successfully.');
    }
}
