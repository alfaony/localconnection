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
        Schema::table('fine_tune_files', function (Blueprint $table) {
            $table->string('fine_tune_file_id')->nullable(true)->after('company_id');
            $table->foreignId('fine_tune_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fine_tune_files', function (Blueprint $table) {
            $table->dropColumn('fine_tune_file_id');
            $table->foreignId('fine_tune_id')->nullable(false)->change();
        });
    }
};
