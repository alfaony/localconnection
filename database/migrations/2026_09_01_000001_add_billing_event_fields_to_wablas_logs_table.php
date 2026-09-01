<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wablas_logs', function (Blueprint $table) {
            $table->string('event_key')->nullable()->after('status')->index();
            $table->date('effective_date')->nullable()->after('event_key')->index();
            $table->string('template_key')->nullable()->after('effective_date');
            $table->index(
                ['source', 'source_id', 'effective_date'],
                'wablas_logs_billing_delivery_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::table('wablas_logs', function (Blueprint $table) {
            $table->dropIndex('wablas_logs_billing_delivery_lookup');
            $table->dropIndex(['event_key']);
            $table->dropIndex(['effective_date']);
            $table->dropColumn(['event_key', 'effective_date', 'template_key']);
        });
    }
};
