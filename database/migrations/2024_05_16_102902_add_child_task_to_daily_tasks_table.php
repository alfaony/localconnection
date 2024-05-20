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
            $table->uuid('child_daily_task_id')->after('assignment_user_id')->nullable();
            $table->foreign('child_daily_task_id')->references('id')->on('daily_tasks')->onDelete('cascade');

            
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
            $table->dropForeign('daily_tasks_child_daily_task_id_foreign');
            $table->dropColumn('child_daily_task_id');
        });
    }
};
