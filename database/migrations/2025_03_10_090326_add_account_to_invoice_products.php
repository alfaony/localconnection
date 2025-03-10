<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up()
    {
        Schema::table('invoice_products', function (Blueprint $table) {
            $table->string('account')->nullable()->after('product_id');
        });
    }

    public function down()
    {
        Schema::table('invoice_products', function (Blueprint $table) {
            $table->dropColumn('account');
        });
    }
};