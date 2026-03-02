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
    // 
    public function up()
    {
        Schema::table('used_laptop_media', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('used_laptop_id');
        });
    }

    public function down()
    {
        Schema::table('used_laptop_media', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
