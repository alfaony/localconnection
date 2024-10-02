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
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('status_position')->unsigned()->nullable()->after('slug');
            $table->string('id_card')->nullable()->after('status_position');
            $table->text('address')->nullable()->after('id_card');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status_position');
            $table->dropColumn('id_card');
            $table->dropColumn('address');
        });
    }
};
