<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_suppliers', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->string('owner_name'); // Pemilik Toko
            $table->string('store_name'); // Nama Toko
            $table->string('phone_number')->nullable(); // No HP
            $table->string('location')->nullable(); // Tempat
            $table->text('sales_information')->nullable(); // Informasi Penjualan
            $table->text('additional_information')->nullable(); // Informasi Tambahan
            $table->string('store_photo')->nullable(); // Foto Toko
            $table->string('ktp_photo')->nullable(); // Foto KTP
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_suppliers');
    }
};