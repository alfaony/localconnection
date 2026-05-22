<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('report_links', function (Blueprint $table) {
            $table->text('description')->nullable()->after('link');
        });
    }

    public function down()
    {
        Schema::table('report_links', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
