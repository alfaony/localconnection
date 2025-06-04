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
        Schema::table('potential_vendors', function (Blueprint $table) {
            $table->string('status')->nullable()->after('product_supplier_id');
            $table->bigInteger('price_offered')->nullable()->after('status');
            $table->text('note')->nullable()->after('price_offered');
            $table->string('response_token', 64)->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('potential_vendors', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('price_offered');
            $table->dropColumn('note');
            $table->dropColumn('response_token');
        });
    }
};
