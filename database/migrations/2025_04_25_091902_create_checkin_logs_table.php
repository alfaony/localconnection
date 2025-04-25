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
        Schema::create('checkin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_checkin_id')->constrained('employee_checkings')->onDelete('cascade');
            $table->timestamp('excecuted_in_at');
            $table->timestamp('excecuted_out_at')->nullable();
            $table->json('response_fcm')->nullable();
            $table->json('response_firebase')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checkin_logs');
    }
};
