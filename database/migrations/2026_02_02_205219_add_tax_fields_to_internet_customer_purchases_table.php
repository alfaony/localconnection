<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('internet_customer_purchases', function (Blueprint $table) {
            $table->decimal('amount_before_tax', 15, 2)->nullable()->after('discount_amount')->comment('Net amount before tax');
            $table->decimal('tax_rate', 5, 2)->nullable()->after('amount_before_tax')->comment('Tax rate percentage (e.g., 11 for 11%)');
            $table->decimal('tax_amount', 15, 2)->nullable()->after('tax_rate')->comment('Tax amount in currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internet_customer_purchases', function (Blueprint $table) {
            $table->dropColumn(['amount_before_tax', 'tax_rate', 'tax_amount']);
        });
    }
};
