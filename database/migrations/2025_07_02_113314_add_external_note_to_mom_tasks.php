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
        Schema::table('mom_tasks', function (Blueprint $table) {
            $table->text('external_note')->nullable()->after('end_date');
            $table->text('reject_reason')->nullable()->after('external_note');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mom_tasks', function (Blueprint $table) {
            $table->dropColumn('external_note');
            $table->dropColumn('reject_reason');
        });
    }
};
