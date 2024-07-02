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
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('pic_in')->after('clock_out')->nullable();
            $table->string('pic_out')->after('pic_in')->nullable();
            $table->string('status_in')->after('pic_out')->nullable();
            $table->string('status_out')->after('status_in')->nullable();
            $table->string('note')->after('status_in')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('pic_in');
            $table->dropColumn('pic_out');
            $table->dropColumn('status_in');
            $table->dropColumn('status_out');
            $table->dropColumn('note');
        });
    }
};
