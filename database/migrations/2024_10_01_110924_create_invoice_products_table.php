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
        Schema::create('invoice_products', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID sebagai primary key
            $table->uuid('invoice_id');
            $table->uuid('product_id');
            $table->integer('qty')->nullable();
            $table->integer('sort')->nullable(true);
            $table->integer('price_sell')->nullable(true);
            $table->text('description')->nullable();
            $table->bigInteger('sub_total')->nullable()->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_products');
    }
};
