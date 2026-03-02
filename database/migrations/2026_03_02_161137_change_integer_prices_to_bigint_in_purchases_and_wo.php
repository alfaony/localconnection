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
        Schema::table('work_order_products', function (Blueprint $table) {
            $table->unsignedBigInteger('price_buy')->nullable()->change();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->nullable()->change();
            $table->unsignedBigInteger('sub_total_price')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_order_products', function (Blueprint $table) {
            $table->integer('price_buy')->nullable()->change();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->integer('price')->nullable()->change();
            $table->integer('sub_total_price')->nullable()->change();
        });
    }
};
