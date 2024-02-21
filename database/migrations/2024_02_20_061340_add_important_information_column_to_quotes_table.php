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
        Schema::table('quotes', function (Blueprint $table) {
            $table->boolean('budget_transition')->nullable()->default(false)->after('customer_id');
            $table->string('quote_transition')->nullable()->after('budget_transition');
            $table->string('payment_term')->nullable()->after('quote_transition');
            $table->string('third_party_docs')->nullable()->after('payment_term');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('budget_transition');
            $table->dropColumn('quote_transition');
            $table->dropColumn('payment_term');
            $table->dropColumn('third_party_docs');
        });
    }
};
