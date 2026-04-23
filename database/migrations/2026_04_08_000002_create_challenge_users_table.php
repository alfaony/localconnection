<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('challenge_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('challenge_id');
            $table->uuid('user_id');
            $table->uuid('invited_by');
            $table->boolean('reward_given')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('challenge_id')->references('id')->on('challenges')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['challenge_id', 'user_id']);
            $table->index(['user_id', 'challenge_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('challenge_users');
    }
};
