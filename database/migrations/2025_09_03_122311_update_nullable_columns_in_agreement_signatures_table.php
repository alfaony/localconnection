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
        Schema::table('agreement_signatures', function (Blueprint $table) {
            $table->string('image_ktp')->nullable()->change();
            $table->string('signature')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agreement_signatures', function (Blueprint $table) {
            $table->string('image_ktp')->nullable(false)->change();
            $table->string('signature')->nullable(false)->change();
        });
    }
};
