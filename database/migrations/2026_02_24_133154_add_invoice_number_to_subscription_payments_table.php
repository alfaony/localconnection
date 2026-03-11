<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceNumberToSubscriptionPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            // Format: SS-YYYYMMDD-XXXXX (maks ~18 karakter, aman untuk Midtrans)
            $table->string('invoice_number', 50)->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropColumn('invoice_number');
        });
    }
}
