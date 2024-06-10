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
        Schema::create('daily_task_objective_key_result', function (Blueprint $table) {
            $table->id();
            $table->uuid('daily_task_id');
            $table->uuid('objective_key_result_id');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('daily_task_id')->references('id')->on('daily_tasks')->onDelete('cascade');
            $table->foreign('objective_key_result_id')->references('id')->on('objective_key_results')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_task_objective_key_result');
    }
};
