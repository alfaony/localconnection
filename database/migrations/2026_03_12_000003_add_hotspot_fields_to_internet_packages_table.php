<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internet_packages', function (Blueprint $table) {
            // 0 = unlimited. Dalam detik. Berlaku untuk hotspot member.
            $table->unsignedInteger('session_timeout_seconds')->nullable()->default(null)->after('quota_bytes');
            $table->unsignedInteger('idle_timeout_seconds')->nullable()->default(null)->after('session_timeout_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('internet_packages', function (Blueprint $table) {
            $table->dropColumn(['session_timeout_seconds', 'idle_timeout_seconds']);
        });
    }
};
