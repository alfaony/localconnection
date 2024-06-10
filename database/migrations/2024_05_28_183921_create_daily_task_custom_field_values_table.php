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
        Schema::create('daily_task_custom_field_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('daily_task_id');
            $table->uuid('custom_field_id');
            $table->uuid('custom_field_value_id')->nullable(); // Optional for multi-select
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('daily_task_id')->references('id')->on('daily_tasks')->onDelete('cascade');
            $table->foreign('custom_field_id')->references('id')->on('daily_task_project_custom_fields')->onDelete('cascade');
            $table->foreign('custom_field_value_id')->references('id')->on('daily_task_project_custom_field_values')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_task_custom_field_values');
    }
};
