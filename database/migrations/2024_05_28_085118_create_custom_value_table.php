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
        // CreateDailyTaskProjectCustomFieldsTable
        Schema::create('daily_task_project_custom_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('daily_task_project_id');
            $table->string('name');
            $table->string('type');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('daily_task_project_id')->references('id')->on('daily_task_projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_task_project_custom_fields');
    }
};
