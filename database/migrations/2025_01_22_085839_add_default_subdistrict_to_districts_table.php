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
        Schema::table('districts', function (Blueprint $table) {
            $table->unsignedBigInteger('default_subdistrict_id')->nullable()->after('name');
            $table->foreign('default_subdistrict_id')->references('id')->on('subdistricts')->onDelete('set null');
            $table->dropColumn('is_default');
        });
    }

    public function down()
    {
        Schema::table('districts', function (Blueprint $table) {
            $table->dropForeign(['default_subdistrict_id']);
            $table->dropColumn('default_subdistrict_id');
            $table->boolean('is_default')->default(false)->after('name');
        });
    }
};
