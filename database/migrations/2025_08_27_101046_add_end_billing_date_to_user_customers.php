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
        Schema::table('user_customers', function (Blueprint $table) {
            $table->date('end_billing_date')->nullable()->after('start_billing_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_customers', function (Blueprint $table) {
            $table->dropColumn('end_billing_date');
        });
    }
};
