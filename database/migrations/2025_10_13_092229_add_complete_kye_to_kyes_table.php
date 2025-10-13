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
        Schema::table('kyes', function (Blueprint $table) {
            $table->string('call_name')->nullable()->after('full_name');
            $table->enum('gender', ['male', 'female'])->nullable()->after('call_name');
            $table->string('npwp_photo')->nullable()->after('gender');
            $table->string('marital_status')->nullable()->after('npwp_photo');
            $table->integer('number_of_children')->nullable()->after('marital_status');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kyes', function (Blueprint $table) {
            $table->dropColumn('call_name');
            $table->dropColumn('gender');
            $table->dropColumn('npwp_photo');
            $table->dropColumn('marital_status');
            $table->dropColumn('number_of_children');
        });
    }
};
