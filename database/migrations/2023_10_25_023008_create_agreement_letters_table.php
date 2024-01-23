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
        Schema::create('agreement_letters', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID sebagai primary key
            $table->uuid('user_created_id');
            $table->uuid('user_updated_id');
            $table->uuid('quote_id');
            $table->unsignedBigInteger('agreement_letter_number')->nullable()->unique();
            $table->string('number_result')->nullable();
            $table->date('date');
            $table->string('slug')->unique();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_created_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_updated_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('quote_id')->references('id')->on('quotes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agreement_letters');
    }
};
