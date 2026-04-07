<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerTypeToInternetCustomersTable extends Migration
{
    public function up()
    {
        Schema::table('internet_customers', function (Blueprint $table) {
            $table->string('customer_type')->default('rumah')->after('status');
        });
    }

    public function down()
    {
        Schema::table('internet_customers', function (Blueprint $table) {
            $table->dropColumn('customer_type');
        });
    }
}
