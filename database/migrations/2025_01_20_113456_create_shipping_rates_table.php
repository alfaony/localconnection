<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id')->constrained('providers')->onDelete('cascade'); // Provider ID
            $table->foreignUuid('service_type_id')->constrained('service_types')->onDelete('cascade'); // Service Type ID
            $table->unsignedBigInteger('origin_id');
            $table->foreign('origin_id')->references('id')->on('postal_codes')->onDelete('cascade'); // Origin City
            $table->unsignedBigInteger('destination_id');
            $table->foreign('destination_id')->references('id')->on('postal_codes')->onDelete('cascade'); // Destination City

            $table->decimal('base_weight', 15, 2)->default(0); // Basic Shipping Rate
            $table->decimal('base_price', 15, 2)->default(0); // Basic Shipping Rate
            $table->decimal('additional_weight', 15, 2)->nullable(); // Rate per Kilogram
            $table->decimal('additional_price', 15, 2)->nullable(); // Rate per Kilogram
            $table->decimal('rate_per_cbm', 15, 2)->nullable(); // Rate per Cubic Meter
            $table->string('delivery_time')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_rates');
    }
};
