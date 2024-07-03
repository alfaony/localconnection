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
            $table->string('pic_in')->after('clock_out')->nullable(true);
            $table->string('pic_out')->after('pic_in')->nullable(true);
            $table->boolean('ontime_in')->after('pic_out')->nullable(true);
            $table->boolean('ontime_out')->after('ontime_in')->nullable(true);
            $table->string('note')->after('ontime_out')->nullable(true);
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
            $table->dropColumn('ontime_in');
            $table->dropColumn('ontime_out');
            $table->dropColumn('note');
        });
    }
};
