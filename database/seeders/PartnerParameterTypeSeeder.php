<?php

namespace Database\Seeders;

use App\Models\PartnerParameterType;
use Illuminate\Database\Seeder;
use App\Models\Company;

class PartnerParameterTypeSeeder extends Seeder
{
    public function run(): void
    {
        $parameters = [
            [
                'name' => 'Revenue',
                'code' => 'revenue',
                'unit' => 'IDR',
                'description' => 'Total revenue target from partner',
                'sort_order' => 1,
            ],
            [
                'name' => 'Deals',
                'code' => 'deals',
                'unit' => 'count',
                'description' => 'Number of deals closed',
                'sort_order' => 2,
            ],
            [
                'name' => 'Certification',
                'code' => 'certification',
                'unit' => 'count',
                'description' => 'Number of certifications obtained',
                'sort_order' => 3,
            ],
            [
                'name' => 'Training Headcount',
                'code' => 'training_headcount',
                'unit' => 'person',
                'description' => 'Number of people trained',
                'sort_order' => 4,
            ],
            [
                'name' => 'Pipeline Value',
                'code' => 'pipeline_value',
                'unit' => 'IDR',
                'description' => 'Total pipeline value',
                'sort_order' => 5,
            ],
        ];
        
        $company = Company::all();
        
        foreach ($company as $c) {
            foreach ($parameters as $parameter) {
                $parameter['company_id'] = $c->id;

                PartnerParameterType::create($parameter);
            }
        }

        $this->command->info('Partner parameter types seeded successfully!');
    }
}