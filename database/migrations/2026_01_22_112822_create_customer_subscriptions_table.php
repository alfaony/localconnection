<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('user_id');
            $table->uuid('master_account_id');
            $table->uuid('package_id');
            $table->string('order_number')->unique();
            $table->decimal('harga_bayar', 15, 2);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_expired')->nullable();
            $table->enum('status', ['active', 'expired', 'suspended'])->default('active');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('master_account_id')->references('id')->on('master_accounts')->onDelete('restrict');
            $table->foreign('package_id')->references('id')->on('software_packages')->onDelete('restrict');
            
            // Indexes
            $table->index('company_id', 'idx_company');
            $table->index(['user_id', 'status'], 'idx_user_status');
            $table->index('master_account_id', 'idx_master_account');
            $table->index(['tanggal_expired', 'status'], 'idx_expired');
            $table->index('payment_status', 'idx_payment_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_subscriptions');
    }
}