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
        Schema::table('meeting_user', function (Blueprint $table) {
            $table->boolean('is_attended')->default(false);
            $table->time('join_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meeting_user', function (Blueprint $table) {
            $table->dropColumn('is_attended');
            $table->dropColumn('join_time');
        });
    }
};
