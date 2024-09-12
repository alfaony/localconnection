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
            $table->boolean('status')->after('user_id')->default(false); // Boolean status, nullable (true = approved, false = rejected, n
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
            $table->dropColumn('status');
        });
    }
};
