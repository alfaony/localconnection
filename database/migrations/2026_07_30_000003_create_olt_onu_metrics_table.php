<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olt_onu_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_onu_id')->constrained('olt_onus')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('status', 30);
            $table->decimal('rx_power', 8, 2)->nullable();
            $table->decimal('tx_power', 8, 2)->nullable();
            $table->unsignedInteger('distance_m')->nullable();
            $table->timestamp('recorded_at');

            $table->index(['olt_onu_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olt_onu_metrics');
    }
};
