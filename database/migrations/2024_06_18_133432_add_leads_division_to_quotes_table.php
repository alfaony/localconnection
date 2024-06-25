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
            $table->string('leads_from')->nullable()->default('2')->after('customer_id');
            $table->uuid('division_budget_id')->nullable()->after('leads_from');

            $table->foreign('division_budget_id')->references('id')->on('division_budgets')->onDelete('cascade');
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
            $table->dropForeign('quotes_division_budget_id_foreign');
            $table->dropColumn('division_budget_id');
            $table->dropColumn('leads_from');
        });
    }
};
