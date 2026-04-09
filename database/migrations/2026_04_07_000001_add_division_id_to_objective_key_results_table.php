<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('objective_key_results', function (Blueprint $table) {
            $table->uuid('division_id')->nullable()->after('objective_id');
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('objective_key_results', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
        });
    }
};
