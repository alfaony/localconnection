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
        Schema::create('cctv_check_photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cctv_check_id');
            $table->string('path');
            $table->timestamps();
            $table->softDeletes();
        
            $table->foreign('cctv_check_id')->references('id')->on('cctv_checks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cctv_check_photos');
    }
};
