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
        Schema::create('report_project_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_project_id');
            $table->string('name');
            $table->string('link')->nullabel();
            $table->string('report')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('report_project_id')->references('id')->on('report_projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_project_details');
    }
};
