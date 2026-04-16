<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(true)->after('name');
            $table->string('email')->nullable()->change();
            // Unique per company: satu username hanya boleh dipakai sekali per perusahaan
            $table->unique(['company_id', 'username'], 'users_company_username_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_company_username_unique');
            $table->string('email')->nullable(false)->change();
            $table->dropColumn('username');
        });
    }
};
