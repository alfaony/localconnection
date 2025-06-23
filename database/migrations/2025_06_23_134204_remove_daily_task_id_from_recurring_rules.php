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
        Schema::table('recurring_rules', function (Blueprint $table) {
            $table->dropForeign(['daily_task_id']);
            $table->dropColumn('daily_task_id');
        });
    }

    public function down()
    {
        Schema::table('recurring_rules', function (Blueprint $table) {
            $table->uuid('daily_task_id')->nullable();

            $table->foreign('daily_task_id')
                ->references('id')
                ->on('daily_tasks')
                ->onDelete('cascade');
        });
    }
};
