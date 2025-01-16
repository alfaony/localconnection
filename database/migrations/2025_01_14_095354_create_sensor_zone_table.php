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
        Schema::create('sensor_zone', function (Blueprint $table) {
            $table->id();
            $table->uuid('zone_id');
            $table->uuid('sensor_id');
            $table->string('sensor_code')->nullable();
            $table->string('value')->nullable();
            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade');
            $table->foreign('sensor_id')->references('id')->on('sensors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sensor_zone');
    }
};
