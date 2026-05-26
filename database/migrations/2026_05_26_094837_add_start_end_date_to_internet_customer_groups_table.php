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
        Schema::table('internet_customer_groups', function (Blueprint $table) {
            $table->integer('start_day')->nullable()->after('last_number');
            $table->integer('end_day')->nullable()->after('start_day');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internet_customer_groups', function (Blueprint $table) {
            $table->dropColumn('start_day');
            $table->dropColumn('end_day');
        });
    }
};
