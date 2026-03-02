<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('address_pools', function (Blueprint $table) {
            // Hapus constraint unik di kolom name + cidr
            $table->dropUnique(['name', 'cidr']);
        });
    }

    public function down(): void
    {
        Schema::table('address_pools', function (Blueprint $table) {
            // Tambahkan lagi constraint unik kalau rollback
            $table->unique(['name', 'cidr']);
        });
    }
};
