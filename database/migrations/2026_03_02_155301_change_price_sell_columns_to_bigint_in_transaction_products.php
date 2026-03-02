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
        // Schema::table('quote_products', function (Blueprint $table) {
        //     $table->unsignedBigInteger('price_sell')->nullable()->change();
        // });

        // Schema::table('invoice_products', function (Blueprint $table) {
        //     $table->unsignedBigInteger('price_sell')->nullable()->change();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quote_products', function (Blueprint $table) {
            $table->integer('price_sell')->nullable()->change();
        });

        Schema::table('invoice_products', function (Blueprint $table) {
            $table->integer('price_sell')->nullable()->change();
        });
    }
};
