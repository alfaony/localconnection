<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_vouchers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hotspot_voucher_batch_id')->constrained('hotspot_voucher_batches')->cascadeOnDelete();
            $table->foreignUuid('hotspot_server_id')->constrained('hotspot_servers')->cascadeOnDelete();
            $table->foreignId('internet_package_id')->constrained('internet_packages')->cascadeOnDelete();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('status')->default('unused');
            // valid_from = null sampai pertama kali dipakai (login)
            $table->timestamp('valid_from')->nullable();
            // expires_at = valid_from + session_timeout_seconds (dihitung saat first use)
            $table->timestamp('expires_at')->nullable();
            // MAC yang pertama kali pakai voucher ini
            $table->string('used_by_mac', 20)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_vouchers');
    }
};
