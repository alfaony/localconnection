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
        Schema::create('punishment_users', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->references('id')->on('users');
            $table->foreignUuid('dailytask_id')->nullable()->constrained('daily_tasks')->references('id')->on('daily_tasks');
            $table->string('point');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('punishment_users');
    }
};
