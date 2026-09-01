<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('wablas_logs', 'company_id')) {
            Schema::table('wablas_logs', function (Blueprint $table) {
                $table->uuid('company_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('wablas_logs', 'company_id')) {
            Schema::table('wablas_logs', function (Blueprint $table) {
                $table->dropIndex(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};
