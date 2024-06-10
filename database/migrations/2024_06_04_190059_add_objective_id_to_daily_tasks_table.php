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
        Schema::table('daily_tasks', function (Blueprint $table) 
        {
            $table->uuid('objective_id')->nullable(true)->after('slug');
            $table->foreign('objective_id')->references('id')->on('objectives')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->dropForeign('daily_tasks_objective_id_foreign');
            $table->dropColumn('objective_id');
        });
    }
};
