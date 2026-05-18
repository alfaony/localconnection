<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNpwpToInternetCustomersTable extends Migration
{
    public function up()
    {
        Schema::table('internet_customers', function (Blueprint $table) {
            $table->string('npwp_number', 30)->nullable()->after('ktp_photo');
            $table->string('npwp_photo')->nullable()->after('npwp_number');
        });
    }

    public function down()
    {
        Schema::table('internet_customers', function (Blueprint $table) {
            $table->dropColumn(['npwp_number', 'npwp_photo']);
        });
    }
}
