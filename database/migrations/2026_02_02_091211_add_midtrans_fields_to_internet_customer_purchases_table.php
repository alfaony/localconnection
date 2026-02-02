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
        Schema::table('internet_customer_purchases', function (Blueprint $table) {
            $table->string('midtrans_snap_token')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('midtrans_payment_type')->nullable();
            $table->timestamp('midtrans_paid_at')->nullable();
            $table->json('midtrans_raw_response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internet_customer_purchases', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_snap_token',
                'midtrans_transaction_id',
                'midtrans_payment_type',
                'midtrans_paid_at',
                'midtrans_raw_response'
            ]);
        });
    }
};
