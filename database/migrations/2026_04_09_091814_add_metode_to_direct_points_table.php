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
        Schema::table('direct_points', function (Blueprint $table) {
            $table->string('metode')->default('direct_point')->nullable()->after('division_quota_lock_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('direct_points', function (Blueprint $table) {
            $table->dropColumn('metode');
        });
    }
};
