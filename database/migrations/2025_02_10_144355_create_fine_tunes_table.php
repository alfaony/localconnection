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
        Schema::create('fine_tunes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fine_tune_table_id')->constrained('fine_tune_tables')->onDelete('cascade');
            $table->uuid('company_id')->nullable(true);
            $table->string('fine_tune_id');
            $table->string('fine_tune_model');
            $table->string('status');
            $table->boolean('active')->default(false);      
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fine_tunes');
    }
};
