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
        Schema::create('optical_distribution_pop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pop_id')->constrained('pops')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('optical_distribution_id')->constrained('optical_distributions')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('optical_distribution_pop');
    }
};
