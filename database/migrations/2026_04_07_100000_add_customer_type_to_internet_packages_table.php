<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerTypeToInternetPackagesTable extends Migration
{
    public function up()
    {
        Schema::table('internet_packages', function (Blueprint $table) {
            $table->string('customer_type')->default('rumah')->after('type');
        });
    }

    public function down()
    {
        Schema::table('internet_packages', function (Blueprint $table) {
            $table->dropColumn('customer_type');
        });
    }
}
