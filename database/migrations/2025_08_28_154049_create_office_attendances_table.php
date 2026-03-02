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
        Schema::create('office_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies');
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('barcode_attendance_id')->nullable()->constrained('barcode_attendances');
            $table->string('location_lat')->nullable();
            $table->string('location_long')->nullable();
            $table->string('selfie_path')->nullable(); // bisa gunakan Laravel File Storage
            $table->timestamp('time')->nullable();
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
        Schema::dropIfExists('office_attendances');
    }
};
