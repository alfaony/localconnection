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
        Schema::create('vendor_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_supplier_id')->nullable();
            $table->unsignedBigInteger('item_request_id')->nullable();
            $table->string('phone');
            $table->text('message');
            $table->boolean('is_out_of_flow')->default(false);
            $table->boolean('sudah_ingatkan')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_supplier_id')->references('id')->on('product_suppliers')->onDelete('cascade');
            $table->foreign('item_request_id')->references('id')->on('item_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendor_responses');
    }
};
