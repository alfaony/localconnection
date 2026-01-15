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
        Schema::create('internet_purchase_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('internet_purchase_id')->constrained('internet_customer_purchases')->cascadeOnDelete();
            $table->foreignUuid('internet_customer_id')->constrained('internet_customers')->cascadeOnDelete();
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
        Schema::dropIfExists('internet_purchase_coupons');
    }
};
