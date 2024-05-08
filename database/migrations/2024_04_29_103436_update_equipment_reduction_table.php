<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEquipmentReductionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_reductions', function (Blueprint $table) {
            // Change columns to text type
            $table->text('report')->nullable()->change();
            $table->text('found')->nullable()->change();
            $table->text('doing')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment_reductions', function (Blueprint $table) {
            // Change columns back to string type if needed
            $table->string('report', 255)->nullable()->change();
            $table->string('found', 255)->nullable()->change();
            $table->string('doing', 255)->nullable()->change();
        });
    }
}
