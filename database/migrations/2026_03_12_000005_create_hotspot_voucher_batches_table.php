<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_voucher_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('hotspot_server_id')->constrained('hotspot_servers')->cascadeOnDelete();
            $table->foreignId('internet_package_id')->constrained('internet_packages')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('prefix')->nullable(); // prefix username voucher, misal "VOC-"
            $table->foreignUuid('generated_by')->constrained('users')->cascadeOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_voucher_batches');
    }
};
