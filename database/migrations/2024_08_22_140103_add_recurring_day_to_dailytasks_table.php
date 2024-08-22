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
    public function up()
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->json('recurring_days')->nullable(); // Kolom untuk menyimpan hari-hari tertentu dalam bentuk JSON
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->dropColumn('recurring_days');
        });
    }
};
