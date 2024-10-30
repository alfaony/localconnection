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
        Schema::create('national_holidays', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable(); // Keterangan libur nasional, seperti nama libur
            $table->date('date'); // Tanggal libur nasional
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique('date'); // Set tanggal sebagai unique untuk menghindari duplikasi
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('national_holidays');
    }
};
