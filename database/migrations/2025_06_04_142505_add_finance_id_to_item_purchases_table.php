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
        Schema::table('item_purchases', function (Blueprint $table) {
            $table->uuid('finance_user_id')->nullable()->constrained('users')->after('product_supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_purchases', function (Blueprint $table) {
            $table->dropColumn('finance_user_id');
        });
    }
};
