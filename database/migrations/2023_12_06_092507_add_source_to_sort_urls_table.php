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
        Schema::table('sort_urls', function (Blueprint $table) {
            $table->string('source')->nullable(true)->after('id');
            $table->uuid('source_id')->nullable(true)->after('source');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sort_urls', function (Blueprint $table) 
        {
            $table->dropColumn('source');
            $table->dropColumn('source_id');
        });
    }
};
