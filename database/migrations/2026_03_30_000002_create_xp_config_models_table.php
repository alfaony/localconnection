<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('xp_config_models', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('xp_config_id');
            $table->string('source_type');    // 'DailyTask', 'Meeting', dll.
            $table->integer('xp');            // bisa negatif
            $table->string('label')->nullable();
            $table->timestamps();

            $table->foreign('xp_config_id')
                  ->references('id')
                  ->on('xp_configs')
                  ->onDelete('cascade');

            $table->unique(['xp_config_id', 'source_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('xp_config_models');
    }
};
