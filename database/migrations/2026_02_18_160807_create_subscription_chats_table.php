<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscription_chats', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_id'); // UUID FK
            $table->uuid('user_id');
            $table->text('message')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('subscription_id')
                  ->references('id')
                  ->on('customer_subscriptions')
                  ->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_chats');
    }
};
