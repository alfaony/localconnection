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
        Schema::table('security_check_photos', function (Blueprint $table) {
            $table->enum('file_type', ['image', 'video'])->default('image')->after('path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('security_check_photos', function (Blueprint $table) {
            $table->dropColumn('file_type');
        });
    }
};
