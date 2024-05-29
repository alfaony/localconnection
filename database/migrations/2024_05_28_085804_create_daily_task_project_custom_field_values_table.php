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
        // CreateDailyTaskProjectCustomFieldValuesTable
        Schema::create('daily_task_project_custom_field_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('custom_field_id');
            $table->integer('ordering')->default(0);
            $table->string('value');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('custom_field_id')->references('id')->on('daily_task_project_custom_fields')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_task_project_custom_field_values');
    }
};
