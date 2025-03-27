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
        Schema::table('partnership_agreement_types', function (Blueprint $table) {
            $table->integer('count_signature')->default(0)->after('name_format');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partnership_agreement_types', function (Blueprint $table) {
            $table->dropColumn('count_signature');
        });
    }
};
