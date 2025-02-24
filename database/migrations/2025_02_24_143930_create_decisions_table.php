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
        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('answer');
            $table->uuid('user_create_id');
            $table->uuid('user_responsible_id')->nullable();
            $table->uuid('user_accountable_id')->nullable();
            $table->uuid('user_consult_id')->nullable();
            $table->boolean('is_approve')->default(false);
            $table->integer('trust_score')->nullable();
            $table->integer('execution_score')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_create_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_responsible_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_accountable_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_consult_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('decisions');
    }
};
