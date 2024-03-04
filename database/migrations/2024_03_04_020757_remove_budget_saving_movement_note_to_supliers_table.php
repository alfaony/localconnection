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
        Schema::table('supliers', function (Blueprint $table) {
            $table->dropColumn('budget_movement');
            $table->dropColumn('budget_saving');
            $table->dropColumn('note');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supliers', function (Blueprint $table) {
            $table->boolean('budget_movement')->nullable()->default(false)->after('date');
            $table->boolean('budget_saving')->nullable()->default(false)->after('budget_movement');
            $table->text('note')->nullable()->after('budget_saving');
        });
    }
};
