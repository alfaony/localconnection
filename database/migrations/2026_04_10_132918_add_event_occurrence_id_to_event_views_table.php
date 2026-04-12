<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_views', function (Blueprint $table) {
            // Tambah kolom dulu, baru FK
            if (!Schema::hasColumn('event_views', 'event_occurrence_id')) {
                $table->uuid('event_occurrence_id')->nullable()->after('event_id');
            }
            $table->foreign('event_occurrence_id')
                  ->references('id')
                  ->on('event_occurrences')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('event_views', function (Blueprint $table) {
            $table->dropForeign(['event_occurrence_id']);
            $table->dropColumn('event_occurrence_id');
        });
    }
};
