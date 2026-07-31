<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olt_onus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('internet_customer_id')->nullable()
                ->constrained('internet_customers')->cascadeOnUpdate()->nullOnDelete();

            $table->string('pon_port', 100);
            $table->string('onu_index', 100);
            $table->string('serial_number')->nullable();
            $table->string('name')->nullable();
            $table->string('status', 30)->default('UNKNOWN');
            $table->decimal('rx_power', 8, 2)->nullable();
            $table->decimal('tx_power', 8, 2)->nullable();
            $table->unsignedInteger('distance_m')->nullable();
            $table->string('last_down_reason')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_polled_at')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['olt_id', 'pon_port', 'onu_index'], 'olt_onu_position_unique');
            $table->index(['olt_id', 'status']);
            $table->index('serial_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olt_onus');
    }
};
