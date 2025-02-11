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
        Schema::create('fine_tuned_models', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->string('model_id')->nullable();
            $table->string('filename')->nullable();
            $table->string('file_path')->nullable();
            $table->uuid('company_id')->nullable(true);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fine_tuned_models');
    }
};
