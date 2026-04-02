<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->uuid('objective_division_id')->nullable()->after('objective_id');
            $table->foreign('objective_division_id')->references('id')->on('divisions')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->dropForeign(['objective_division_id']);
            $table->dropColumn('objective_division_id');
        });
    }
};
