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
            $table->text('payment_term')->nullable()->change();
            $table->text('period_term')->nullable()->change();
            $table->text('other_term')->nullable()->change();

            $table->text('payment_term_english')->nullable()->change();
            $table->text('period_term_english')->nullable()->change();
            $table->text('other_term_english')->nullable()->change();
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
            $table->string('payment_term')->after('slug')->nullable();
            $table->string('period_term')->after('payment_term')->nullable();
            $table->string('other_term')->after('period_term')->nullable();

            $table->string('payment_term_english')->after('other_term')->nullable(true);
            $table->string('period_term_english')->after('payment_term_english')->nullable(true);
            $table->string('other_term_english')->after('period_term_english')->nullable(true);

        });
    }
};
