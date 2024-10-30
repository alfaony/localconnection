<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeCheckingsTable extends Migration
{
    public function up()
    {
        Schema::create('employee_checkings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('user_id');
            $table->uuid('division_id');
            $table->timestamp('scheduled_time')->nullable(); // Waktu yang dijadwalkan untuk check-in
            $table->timestamp('checkin_start_time')->nullable(); // Waktu mulai check-in
            $table->boolean('is_active')->default(false); // Status aktif saat check-in berjalan
            $table->boolean('is_completed')->default(false); // Status apakah check-in selesai
            $table->string('photo_path')->nullable(); // Path untuk foto jika divisi memerlukan
            $table->decimal('score', 5, 2)->default(0); // Nilai berdasarkan respons
            $table->decimal('location_latitude', 10, 7)->nullable(); // Lokasi latitude
            $table->decimal('location_longitude', 10, 7)->nullable(); // Lokasi longitude
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_checkings');
    }
}