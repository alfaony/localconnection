<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('original_price', 15, 2)->nullable()->after('unit_price');
            $table->decimal('discount_percent', 5, 2)->default(0)->after('original_price');
        });
    }

    public function down()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'discount_percent']);
        });
    }
};
