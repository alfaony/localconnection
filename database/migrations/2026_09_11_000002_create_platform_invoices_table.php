<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('platform_invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');

            // Format 'YYYY-MM', periode yang ditagih (bulan sebelumnya saat invoice dibuat)
            $table->string('period', 7);

            // Rata-rata active_customer_count harian selama periode, dibulatkan
            $table->unsignedInteger('billed_customer_count')->default(0);
            $table->unsignedInteger('rate_per_customer');
            $table->unsignedBigInteger('amount');

            // pending -> paid, atau pending -> overdue (lewat due_date) -> paid
            $table->string('status')->default('pending');
            $table->timestamp('due_date');
            $table->timestamp('paid_at')->nullable();

            $table->string('midtrans_order_id')->nullable()->unique();
            $table->text('midtrans_snap_token')->nullable();
            $table->text('midtrans_payload')->nullable();

            // Diisi kalau status diubah manual oleh superadmin platform (safety valve)
            $table->uuid('manually_confirmed_by')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'period']);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('platform_invoices');
    }
};
