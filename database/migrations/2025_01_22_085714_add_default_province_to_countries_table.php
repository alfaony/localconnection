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
        Schema::table('countries', function (Blueprint $table) {
            $table->unsignedBigInteger('default_province_id')->nullable()->after('name');
            $table->foreign('default_province_id')->references('id')->on('provinces')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropForeign(['default_province_id']);
            $table->dropColumn('default_province_id');
        });
    }
};
