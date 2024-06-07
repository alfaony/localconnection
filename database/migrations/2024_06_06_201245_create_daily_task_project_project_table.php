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
        Schema::create('daily_task_project_project', function (Blueprint $table) {
            $table->id();
            $table->uuid('daily_task_project_id');
            $table->uuid('project_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('daily_task_project_id')->references('id')->on('daily_task_projects')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_task_project_project');
    }
};
