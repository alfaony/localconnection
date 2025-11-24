<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('internet_customer_purchases', function (Blueprint $table) {
            $table->integer('payment_months')->default(1)->after('amount_paid');
            $table->date('period_start')->nullable()->after('payment_months');
            $table->date('period_end')->nullable()->after('period_start');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('amount_paid');
            $table->decimal('total_before_discount', 15, 2)->nullable()->after('discount_amount');
        });
    }

    public function down()
    {
        Schema::table('internet_customer_purchases', function (Blueprint $table) {
            $table->dropColumn([
                'payment_months',
                'period_start',
                'period_end',
                'discount_amount',
                'total_before_discount'
            ]);
        });
    }
};