<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('user_customers', function (Blueprint $table) {
            // Drop unique index on email
            $table->dropUnique(['email']);
        });
    }

    public function down(): void
    {
        Schema::table('user_customers', function (Blueprint $table) {
            // Restore unique constraint if rollback
            $table->unique('email');
        });
    }
};
