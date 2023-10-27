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
        Schema::create('report_projects', function (Blueprint $table) 
        {
            $table->uuid('id')->primary(); // UUID sebagai primary key
            $table->uuid('user_created_id');
            $table->uuid('user_updated_id');
            $table->unsignedBigInteger('report_project_number')->nullable()->unique();
            $table->string('number_result')->nullable();
            $table->date('date')->nullable();
            $table->uuid('work_order_id');
            $table->uuid('project_id');
            $table->string('link_report')->nullabel();
            $table->string('report_file')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_created_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_updated_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('work_order_id')->references('id')->on('work_orders')->onDelete('cascade');
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
        Schema::dropIfExists('report_projects');
    }
};
