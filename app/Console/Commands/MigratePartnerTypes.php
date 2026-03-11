<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigratePartnerTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'partners:migrate-types';

    protected $description = 'Migrate hardcoded partner types to the partner_types table';

    public function handle()
    {
        $this->info('Starting partner types migration...');

        $partners = \Illuminate\Support\Facades\DB::table('partners')->get();
        $typesConfig = config('partners.partner_types');

        $count = 0;

        foreach ($partners as $partner) {
            $typeName = $typesConfig[$partner->partner_type] ?? $partner->partner_type;

            if (!$typeName) continue;

            // Find or create the partner type for this company
            $partnerType = \Illuminate\Support\Facades\DB::table('partner_types')
                ->where('company_id', $partner->company_id)
                ->where('name', $typeName)
                ->first();

            if (!$partnerType) {
                $typeId = \Illuminate\Support\Str::uuid()->toString();
                \Illuminate\Support\Facades\DB::table('partner_types')->insert([
                    'id' => $typeId,
                    'company_id' => $partner->company_id,
                    'name' => $typeName,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $typeId = $partnerType->id;
            }

            // Update partner
            \Illuminate\Support\Facades\DB::table('partners')
                ->where('id', $partner->id)
                ->update(['partner_type_id' => $typeId]);
                
            $count++;
        }

        $this->info("Successfully migrated {$count} partners.");

        return Command::SUCCESS;
    }
}
