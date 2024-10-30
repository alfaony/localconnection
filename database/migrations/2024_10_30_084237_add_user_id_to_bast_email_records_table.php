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
        Schema::table('bast_email_records', function (Blueprint $table) {
            $table->uuid('user_id')->nullable()->after('bast_id');
            $table->unsignedBigInteger('bast_file_merge_id')->nullable()->after('bast_id');

            $table->foreign('bast_file_merge_id')->references('id')->on('bast_file_merges')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bast_email_records', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['bast_file_merge_id']);
            
            $table->dropColumn('user_id');
            $table->dropColumn('bast_file_merge_id');
        });
    }
};
