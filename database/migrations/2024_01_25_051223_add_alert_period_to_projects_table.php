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
        Schema::table('projects', function (Blueprint $table) 
        {
            $table->boolean('recurring')->default(0)->after('work_order_id');
            $table->boolean('alert_expired')->default(0)->after('recurring');
            $table->boolean('alert_one_week')->default(0)->after('alert_expired');
            $table->boolean('alert_two_week')->default(0)->after('alert_one_week');
            $table->boolean('alert_one_month')->default(0)->after('alert_two_week');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) 
        {
            $table->dropColumn('recurring');
            $table->dropColumn('alert_expired');
            $table->dropColumn('alert_one_week');
            $table->dropColumn('alert_two_week');
            $table->dropColumn('alert_one_month');
        });
    }
};
