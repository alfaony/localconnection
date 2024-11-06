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
        Schema::table('users', function (Blueprint $table) 
        {
            $table->boolean('is_checkin')->nullable()->default(1);
            $table->boolean('manual_checkin')->nullable()->default(0);
            $table->boolean('requires_photo')->default(false); // Apakah memerlukan foto
            $table->boolean('requires_location')->default(false); // Apakah memerlukan lokasi
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->time('rest_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_checkin');
            $table->dropColumn('manual_checkin');
            $table->dropColumn('requires_photo');
            $table->dropColumn('requires_location');
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
            $table->dropColumn('rest_time');
        });
    }
};
