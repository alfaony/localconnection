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
            $table->foreignUuid('origin_id')->constrained('districts')->onDelete('cascade'); // Origin City
            $table->foreignUuid('destination_id')->constrained('districts')->onDelete('cascade'); // Destination City

            $table->decimal('base_rate', 15, 2)->default(0); // Basic Shipping Rate
            $table->decimal('base_price', 15, 2)->default(0); // Basic Shipping Rate
            $table->decimal('additional_weight', 15, 2)->nullable(); // Rate per Kilogram
            $table->decimal('additional_price', 15, 2)->nullable(); // Rate per Kilogram
            $table->decimal('rate_per_cbm', 15, 2)->nullable(); // Rate per Cubic Meter
            $table->decimal('minimum_weight', 10, 2)->nullable(); // Minimum Weight (kg)
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
