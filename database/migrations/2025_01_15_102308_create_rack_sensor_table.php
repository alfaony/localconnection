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
        Schema::create('rack_sensor', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('rack_id')->constrained('racks')->onDelete('cascade');
            $table->foreignUuid('sensor_id')->constrained('sensors')->onDelete('cascade');
            $table->string('sensor_code');
            $table->string('value');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rack_sensor');
    }
};
