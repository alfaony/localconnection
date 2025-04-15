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
        Schema::create('dayoff_quotas', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->foreignId('dayoff_type_id')->constrained()->onDelete('cascade');
        
            $table->integer('quota'); // total kuota
            $table->integer('used')->default(0); // jumlah yang sudah digunakan
        
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dayoff_quotas');
    }
};
