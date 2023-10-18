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
        Schema::create('customers', function (Blueprint $table) 
        {
            $table->uuid('id')->primary(); // UUID sebagai primary key
            $table->uuid('user_created_id');
            $table->uuid('user_updated_id');

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('director');
            $table->string('pic');
            $table->string('assignor');
            $table->text('address');
            $table->string('phone');
            $table->string('email');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_created_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_updated_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
