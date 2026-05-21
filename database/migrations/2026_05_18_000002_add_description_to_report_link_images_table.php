<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('report_link_images', function (Blueprint $table) {
            $table->string('description')->nullable()->after('path');
        });
    }

    public function down()
    {
        Schema::table('report_link_images', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
