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
        Schema::table('agreement_letters', function (Blueprint $table) {
            $table->string('payment_term_english')->after('other_term')->nullable(true);
            $table->string('period_term_english')->after('payment_term_english')->nullable(true);
            $table->string('other_term_english')->after('period_term_english')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agreement_letters', function (Blueprint $table) {
            $table->dropColumn('payment_term_english');
            $table->dropColumn('period_term_english');
            $table->dropColumn('other_term_english');
        });
    }
};
