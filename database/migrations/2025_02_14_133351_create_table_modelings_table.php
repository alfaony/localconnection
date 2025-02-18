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
        Schema::create('table_modelings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fine_tune_table_id')->constrained('fine_tune_tables')->onDelete('cascade');
            $table->uuid('company_id')->nullable(true);
            $table->json('data_model')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_modelings');
    }
};
