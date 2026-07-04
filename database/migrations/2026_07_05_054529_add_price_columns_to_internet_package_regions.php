<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('internet_package_regions', function (Blueprint $table) {
            // Nullable: jika null, harga mengikuti harga default paket (price / price_nett)
            $table->decimal('price', 15, 2)->nullable()->after('region_id');
            $table->decimal('price_nett', 15, 2)->nullable()->after('price');
        });
    }

    public function down()
    {
        Schema::table('internet_package_regions', function (Blueprint $table) {
            $table->dropColumn(['price', 'price_nett']);
        });
    }
};
