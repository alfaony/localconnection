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
        Schema::create('daily_task_media', function (Blueprint $table) 
        {
            $table->uuid('id')->primary();
            $table->uuid('daily_task_id');
            $table->string('file_path');
            $table->string('file_type');
            $table->timestamps();
            $table->softDeletes();
        
            $table->foreign('daily_task_id')->references('id')->on('daily_tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_task_media');
    }
};
