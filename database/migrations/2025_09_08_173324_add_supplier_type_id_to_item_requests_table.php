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
        Schema::table('item_requests', function (Blueprint $table) {
            $table->foreignId('supplier_type_id')->nullable(true)->constrained('supplier_types')->cascadeOnDelete()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_requests', function (Blueprint $table) {
            $table->dropForeign(['supplier_type_id']);
            $table->dropColumn('supplier_type_id');
        });
    }
};
