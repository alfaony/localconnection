<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('internet_package_regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internet_package_id')
                ->constrained('internet_packages')
                ->onDelete('cascade');

            // Tipe wilayah: province, city, district
            $table->enum('region_type', ['province', 'city', 'district']);

            // ID wilayah sesuai region_type
            // Tidak FK langsung karena bisa merujuk ke 3 tabel berbeda
            $table->unsignedBigInteger('region_id');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Satu paket hanya bisa punya satu entry per wilayah yang sama
            // Nama index diperpendek agar <= 64 karakter (batasan MySQL)
            $table->unique(['internet_package_id', 'region_type', 'region_id'], 'ipr_pkg_type_region_unique');

            // Index untuk query wilayah
            $table->index(['region_type', 'region_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('internet_package_regions');
    }
};
