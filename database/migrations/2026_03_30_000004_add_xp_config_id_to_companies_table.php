<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->uuid('xp_config_id')->nullable()->after('id');

            $table->foreign('xp_config_id')
                  ->references('id')
                  ->on('xp_configs')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['xp_config_id']);
            $table->dropColumn('xp_config_id');
        });
    }
};
