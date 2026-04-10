<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Tambah kolom baru
            $table->string('color')->default('#667eea')->after('image');
            $table->date('start_date')->nullable()->change();  // sudah ada, pastikan nullable sementara
            $table->boolean('is_routine')->default(false)->after('end_date');
            $table->date('routine_end_date')->nullable()->after('is_routine');

            // Hapus kolom lama (jika masih ada)
            if (Schema::hasColumn('events', 'event_type')) {
                $table->dropColumn('event_type');
            }
            if (Schema::hasColumn('events', 'routine_type')) {
                $table->dropColumn('routine_type');
            }
            if (Schema::hasColumn('events', 'routine_value')) {
                $table->dropColumn('routine_value');
            }
            if (Schema::hasColumn('events', 'event_date')) {
                $table->dropColumn('event_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['color', 'is_routine', 'routine_end_date']);
            $table->string('event_type')->default('specific');
            $table->string('routine_type')->nullable();
            $table->string('routine_value')->nullable();
            $table->date('event_date')->nullable();
        });
    }
};
