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
        Schema::create('coverage_service_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coverage_service_id')->constrained('coverage_services')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('optical_distribution_id')->constrained('optical_distributions')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('coverage_service_distributions');
    }
};
