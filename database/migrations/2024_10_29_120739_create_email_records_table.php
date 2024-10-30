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
        Schema::create('bast_email_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('bast_id');
            $table->uuid('user_id')->nullable();
            $table->json('to'); // Store as JSON
            $table->json('cc')->nullable(); // Store as JSON
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('bast_id')->references('id')->on('basts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bast_email_records');
    }
};
