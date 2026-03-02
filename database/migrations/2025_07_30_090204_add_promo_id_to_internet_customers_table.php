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
        Schema::table('internet_customers', function (Blueprint $table) {
            $table->foreignId('promo_id')->nullable()->constrained('promos')->onDelete('cascade')->after('partnership_agreement_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internet_customers', function (Blueprint $table) {
            $table->dropForeign(['promo_id']);
            $table->dropColumn('promo_id');
        });
    }
};
