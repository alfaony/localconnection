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
        Schema::table('report_projects', function (Blueprint $table) {
            $table->boolean('is_approve')->default(1)->nullable()->after('id');
            $table->text('note')->nullable()->after('is_approve');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_projects', function (Blueprint $table) {
            $table->dropColumn('is_approve');
            $table->dropColumn('note');
        });
    }
};
