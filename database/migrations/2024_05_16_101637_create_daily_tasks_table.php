<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyTasksTable extends Migration
{
    public function up()
    {
        Schema::create('daily_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->uuid('user_id');
            $table->uuid('daily_task_category_id')->nullable();
            $table->uuid('daily_task_type_id')->nullable();
            $table->uuid('assignment_user_id')->nullable();
            $table->uuid('task_status_id');
            $table->date('submit')->nullable(true);
            $table->string('status_submit')->nullable(true);
            $table->boolean('approved')->default(false);  // Status persetujuan
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('point')->default(0);
            $table->text('report_note')->nullable(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('daily_task_category_id')->references('id')->on('daily_task_categories')->onDelete('cascade');
            $table->foreign('daily_task_type_id')->references('id')->on('daily_task_types')->onDelete('cascade');
            $table->foreign('assignment_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('task_status_id')->references('id')->on('task_statuses')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_tasks');
    }
}
