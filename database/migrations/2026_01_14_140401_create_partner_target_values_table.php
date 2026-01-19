<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_target_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('partner_target_id');
            $table->uuid('parameter_type_id');
            $table->bigInteger('target_value')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('partner_target_id')->references('id')->on('partner_targets')->onDelete('cascade');
            $table->foreign('parameter_type_id')->references('id')->on('partner_parameter_types')->onDelete('cascade');
            // $table->unique(['partner_target_id', 'parameter_type_id'], 'unique_target_parameter');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_target_values');
    }
};