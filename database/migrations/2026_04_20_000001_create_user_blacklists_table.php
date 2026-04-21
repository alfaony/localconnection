<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_blacklists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('user_id')->nullable(); // FK ke users jika diimport dari user tidak aktif
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('id_card')->nullable();
            $table->string('avatar')->nullable();
            $table->string('address')->nullable();
            $table->text('reason')->nullable(); // Alasan di-blacklist
            $table->uuid('blacklisted_by')->nullable(); // User yang melakukan blacklist
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('blacklisted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_blacklists');
    }
};
