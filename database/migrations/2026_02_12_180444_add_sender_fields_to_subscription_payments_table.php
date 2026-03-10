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
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->string('manual_transfer_sender_name')->nullable()->after('manual_transfer_proof');
            $table->string('manual_transfer_sender_bank')->nullable()->after('manual_transfer_sender_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropColumn(['manual_transfer_sender_name', 'manual_transfer_sender_bank']);
        });
    }
};
