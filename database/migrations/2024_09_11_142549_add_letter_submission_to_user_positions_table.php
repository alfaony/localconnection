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
        Schema::table('user_positions', function (Blueprint $table) {
            $table->uuid('letter_submission_id')->nullable()->after('user_id');
            $table->foreign('letter_submission_id')->references('id')->on('letter_submissions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_positions', function (Blueprint $table) {
            $table->dropForeign(['letter_submission_id']);
            $table->dropColumn('letter_submission_id');
        });
    }
};
