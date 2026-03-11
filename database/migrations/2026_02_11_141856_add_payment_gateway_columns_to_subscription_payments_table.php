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
        Schema::table('subscription_payments', function (Blueprint $table) {
            // Payment gateway type
            $table->enum('payment_gateway', ['manual', 'xendit', 'midtrans'])
                ->default('xendit')
                ->after('subscription_id');
            
            // Manual transfer fields
            $table->string('manual_transfer_bank', 100)->nullable()->after('payment_gateway');
            $table->string('manual_transfer_account_name', 200)->nullable()->after('manual_transfer_bank');
            $table->string('manual_transfer_account_number', 100)->nullable()->after('manual_transfer_account_name');
            $table->string('manual_transfer_proof')->nullable()->after('manual_transfer_account_number');
            
            // Midtrans fields
            $table->string('midtrans_snap_token')->nullable()->after('manual_transfer_proof');
            $table->string('midtrans_order_id')->nullable()->after('midtrans_snap_token');
            
            // Add index for payment_gateway
            $table->index('payment_gateway', 'idx_payment_gateway');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_payment_gateway');
            
            // Drop columns
            $table->dropColumn([
                'payment_gateway',
                'manual_transfer_bank',
                'manual_transfer_account_name',
                'manual_transfer_account_number',
                'manual_transfer_proof',
                'midtrans_snap_token',
                'midtrans_order_id',
            ]);
        });
    }
};
