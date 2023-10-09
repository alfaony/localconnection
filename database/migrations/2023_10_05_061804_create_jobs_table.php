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
        Schema::create('jobs', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID sebagai primary key
            $table->uuid('manager_id');
            $table->uuid('employee_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total');

            $table->timestamps();
            $table->softDeletes();            

            $table->foreign('manager_id')->references('id')->on('managers')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
};
