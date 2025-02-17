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
        Schema::table('letter_submissions', function (Blueprint $table) {
            $table->bigInteger('letter_number')->nullable();
            $table->string('number_result')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('letter_submissions', function (Blueprint $table) {
            $table->dropColumn('letter_number');
            $table->dropColumn('number_result');
        });
    }
};
