<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_servers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('router_id')->constrained('routers')->cascadeOnDelete();
            $table->foreignUuid('interface_id')->nullable()->constrained('router_interfaces')->nullOnDelete();
            $table->string('name'); // nama hotspot server di MikroTik
            $table->foreignUuid('address_pool_id')->nullable()->constrained('address_pools')->nullOnDelete();
            $table->string('profile_name')->nullable(); // hotspot server profile di MikroTik
            $table->string('dns_name')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_servers');
    }
};
