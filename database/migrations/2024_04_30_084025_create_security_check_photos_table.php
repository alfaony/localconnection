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
        Schema::create('security_check_photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('security_check_id');
            $table->enum('status_of_day', ['check_in', 'check_out']);
            $table->string('path');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('security_check_id')->references('id')->on('security_checks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('security_check_photos');
    }
};
