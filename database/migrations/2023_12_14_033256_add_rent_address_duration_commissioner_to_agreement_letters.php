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
            $table->string('commission_name')->nullable(true)->after('other_term_english');
            $table->string('commission_phone')->nullable(true)->after('commission_name');
            $table->text('commission_address')->nullable(true)->after('commission_phone');
            $table->text('rent_address')->nullable(true)->after('commission_address');
            $table->date('rent_start_duration')->nullable(true)->after('rent_address');
            $table->date('rent_end_duration')->nullable(true)->after('rent_start_duration');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agreement_letters', function (Blueprint $table) 
        {
            $table->dropColumn('commission_name');
            $table->dropColumn('commission_phone');
            $table->dropColumn('commission_address');
            $table->dropColumn('rent_address');
            $table->dropColumn('rent_start_duration');
            $table->dropColumn('rent_end_duration');
        });
    }
};
