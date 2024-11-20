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
        Schema::table('employee_checkings', function (Blueprint $table) {
            $table->uuid('pass_checking_id')->nullable()->after('location_longitude');
            $table->boolean('is_pass')->nullable()->default(false)->after('is_completed');
            $table->boolean('is_permission')->nullable()->default(false)->after('is_pass');
            $table->foreign('pass_checking_id')->references('id')->on('pass_checkings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_checkings', function (Blueprint $table) {
            $table->dropForeign(['pass_checking_id']);
            $table->dropColumn('is_permission');
            $table->dropColumn('is_pass');
            $table->dropColumn('pass_checking_id');
        });
    }
};
