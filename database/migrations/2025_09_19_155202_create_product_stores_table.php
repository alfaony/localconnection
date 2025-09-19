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
        Schema::create('product_stores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('barcode')->nullable();
            $table->unsignedBigInteger('category_product_store_id')->nullable();
            $table->unsignedBigInteger('brand_product_store_id')->nullable();
            $table->string('name');
            $table->text('variant')->nullable();
            $table->text('specification')->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->string('dimension')->nullable(); // ex: "10 x 20 x 30"
            $table->decimal('weight', 8, 2)->nullable();
            $table->BigInteger('selling_price')->nullable();
            $table->foreignUuid('user_create_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('user_modified_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->onDelete('set null');

            $table->softDeletes();
            $table->timestamps();

            // Optional: Add foreign key constraints
            $table->foreign('category_product_store_id')->references('id')->on('category_product_stores')->onDelete('set null');
            $table->foreign('brand_product_store_id')->references('id')->on('brand_product_stores')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_stores');
    }
};
