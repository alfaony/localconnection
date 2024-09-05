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
        Schema::table('letter_types', function (Blueprint $table) {
            $table->uuid('head_letter_types_id')->nullable()->after('name');
            $table->foreign('head_letter_types_id')->references('id')->on('letter_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('letter_types', function (Blueprint $table) {
            $table->dropForeign(['head_letter_types_id']);
            $table->dropColumn('head_letter_types_id');
        });
    }
};
