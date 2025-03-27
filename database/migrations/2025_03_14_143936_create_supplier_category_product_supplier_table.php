<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplier_category_product_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_category_id')->constrained('supplier_categories')->onDelete('cascade'); // Relasi ke supplier_categories
            $table->foreignId('product_supplier_id')->constrained('product_suppliers')->onDelete('cascade'); // Relasi ke product_suppliers
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_category_product_supplier');
    }
};
