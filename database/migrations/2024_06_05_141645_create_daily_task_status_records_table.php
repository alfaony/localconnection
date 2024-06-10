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
        Schema::create('daily_task_status_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('daily_task_id');
            $table->uuid('task_status_id');
            $table->date('date');
            $table->foreign('task_status_id')->references('id')->on('task_statuses')->onDelete('cascade');
            $table->foreign('daily_task_id')->references('id')->on('daily_tasks')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_task_status_records');
    }
};
