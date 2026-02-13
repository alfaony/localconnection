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
            // Add PPN-related fields after amount
            $table->decimal('subtotal', 15, 2)->nullable()->after('amount')->comment('Amount before PPN');
            $table->decimal('ppn_rate', 5, 2)->nullable()->after('subtotal')->comment('PPN percentage (e.g., 11.00 for 11%)');
            $table->decimal('ppn_amount', 15, 2)->nullable()->after('ppn_rate')->comment('Calculated PPN amount');
            
            // Note: 'amount' field already exists and will store the total (subtotal + PPN)
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
            $table->dropColumn(['subtotal', 'ppn_rate', 'ppn_amount']);
        });
    }
};
