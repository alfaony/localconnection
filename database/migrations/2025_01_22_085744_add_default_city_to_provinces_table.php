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
        Schema::table('provinces', function (Blueprint $table) {
            $table->unsignedBigInteger('default_city_id')->nullable()->after('name');
            $table->foreign('default_city_id')->references('id')->on('cities')->onDelete('set null');
            $table->dropColumn('is_default');
        });
    }

    public function down()
    {
        Schema::table('provinces', function (Blueprint $table) {
            $table->dropForeign(['default_city_id']);
            $table->dropColumn('default_city_id');
            $table->boolean('is_default')->default(false)->after('name');
        });
    }
};
