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
        Schema::create('objective_key_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('objective_id');
            $table->string('slug')->unique();
            $table->string('result');
            $table->date('start_date')->nullable(true);
            $table->date('end_date')->nullable(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('objective_id')->references('id')->on('objectives')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('objective_key_results');
    }
};
