<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserStatusTable extends Migration
{
    public function up()
    {
        Schema::create('user_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('user_id');
            $table->string('fcm_id')->nullable(); // Menyimpan FCM ID untuk notifikasi real-time
            $table->timestamp('last_login_at')->nullable(); // Waktu login terakhir
            $table->timestamp('last_scheduled_checkin')->nullable(); // Jadwal check-in terakhir
            $table->boolean('is_online')->default(false); // Status online/offline
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_status');
    }
}

