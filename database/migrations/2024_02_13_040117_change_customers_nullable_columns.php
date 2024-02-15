<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCustomersNullableColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('director')->nullable()->change();
            $table->string('pic')->nullable()->change();
            $table->string('assignor')->nullable()->change();
            $table->string('address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('director')->nullable(false)->change();
            $table->string('pic')->nullable(false)->change();
            $table->string('assignor')->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
        });
    }
}
