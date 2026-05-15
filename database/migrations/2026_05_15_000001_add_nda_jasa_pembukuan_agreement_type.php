<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('partnership_agreement_types')->updateOrInsert(
            ['name' => 'NDA - Jasa Pembukuan'],
            [
                'name_format'     => 'nda_jasa_pembukuan',
                'count_signature' => 2,
                'updated_at'      => now(),
                'created_at'      => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('partnership_agreement_types')
            ->where('name_format', 'nda_jasa_pembukuan')
            ->delete();
    }
};
