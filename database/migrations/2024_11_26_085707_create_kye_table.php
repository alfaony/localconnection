<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKyeTable extends Migration
{
    public function up()
    {
        Schema::create('kyes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Identitas Karyawan
            $table->uuid('user_id');
            $table->string('full_name');
            $table->string('birth_place');
            $table->date('birth_date');
            $table->text('address');
            $table->string('employee_photo')->nullable();
            $table->string('ktp_number');
            $table->string('ktp_photo')->nullable();
            $table->string('selfie_ktp')->nullable();
            $table->string('ktp_family')->nullable();
            $table->string('npwp_number')->nullable();
            $table->string('google_maps')->nullable();
            $table->string('house_photo')->nullable();
            $table->string('skck')->nullable();

            // Informasi Kontak
            $table->string('phone_number');
            $table->string('email')->unique();
            $table->string('imei_number')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();

            // Approval
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('approval_note')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kye');
    }
}
