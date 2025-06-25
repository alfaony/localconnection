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
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('recurring_rule_id')->nullable()->after('recurring_group_id');
            $table->foreign('recurring_rule_id')->references('id')->on('recurring_rules')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->dropForeign(['recurring_rule_id']);
            $table->dropColumn('recurring_rule_id');
        });
    }
};
