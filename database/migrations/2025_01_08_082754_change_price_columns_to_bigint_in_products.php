<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('price_sell')->change();
            $table->unsignedBigInteger('price_buy')->change();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('price_sell')->change();
            $table->unsignedInteger('price_buy')->change();
        });
    }
};
