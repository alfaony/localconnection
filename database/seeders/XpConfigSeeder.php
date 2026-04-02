<?php

namespace Database\Seeders;

use App\Models\XpConfig;
use App\Models\XpConfigModel;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class XpConfigSeeder extends Seeder
{
    /**
     * Buat 1 default XP Config dengan nilai XP per model.
     * Assign ke company sesuai kebutuhan via admin UI atau manual.
     */
    public function run(): void
    {
        $configId = Uuid::uuid4()->toString();

        $config = XpConfig::create([
            'id'          => $configId,
            'name'        => 'Standard',
            'is_enabled'  => true,
            'description' => 'Konfigurasi XP standar untuk semua company.',
        ]);

        $models = [
            ['source_type' => 'ALL',    'xp' => 100,  'label' => 'Default (Semua Aksi)'],
        ];

        foreach ($models as $model) {
            XpConfigModel::create(array_merge($model, [
                'id'           => Uuid::uuid4()->toString(),
                'xp_config_id' => $config->id,
            ]));
        }

        $this->command->info("✅ XpConfig '{$config->name}' berhasil dibuat dengan " . count($models) . " model XP.");
        $this->command->info("   Assign ke company via: Company::find(\$id)->update(['xp_config_id' => '{$config->id}'])");
    }
}
