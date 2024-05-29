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
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->uuid('daily_task_project_id')->nullable(true)->after('task_status_id');
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
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->dropForeign('daily_tasks_daily_task_project_id_foreign');
            $table->dropColumn('daily_task_project_id');
        });
    }
};
