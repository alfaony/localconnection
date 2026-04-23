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
        Schema::table('challenges', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->after('end_date');
        });

        Schema::table('challenge_users', function (Blueprint $table) {
            $table->timestamp('finished_at')->nullable()->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('challenge_users', function (Blueprint $table) {
            $table->dropColumn('finished_at');
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
