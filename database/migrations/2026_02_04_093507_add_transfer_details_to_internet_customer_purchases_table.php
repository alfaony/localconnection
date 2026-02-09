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
            $table->date('transfer_date')->nullable()->after('payment_proof');
            $table->string('transfer_from_bank')->nullable()->after('transfer_date');
            $table->string('transfer_from_account_name')->nullable()->after('transfer_from_bank');
            $table->text('transfer_notes')->nullable()->after('transfer_from_account_name');
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
                'transfer_date',
                'transfer_from_bank',
                'transfer_from_account_name',
                'transfer_notes'
            ]);
        });
    }
};
