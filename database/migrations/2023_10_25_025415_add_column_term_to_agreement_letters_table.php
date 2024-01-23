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
        Schema::table('agreement_letters', function (Blueprint $table) 
        {
            $table->string('payment_term')->after('slug')->nullable();
            $table->string('period_term')->after('payment_term')->nullable();
            $table->string('other_term')->after('period_term')->nullable();
            
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
            $table->dropColumn('payment_term');
            $table->dropColumn('period_term');
            $table->dropColumn('other_term');
        });
    }
};
