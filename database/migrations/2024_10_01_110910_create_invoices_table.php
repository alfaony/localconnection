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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID sebagai primary key
            $table->date('start_date');
            $table->date('end_date');
            $table->date('status')->nullable();
            $table->uuid('quote_id');
            $table->uuid('bast_id');
            $table->uuid('user_created_id');
            $table->uuid('user_updated_id');
            $table->uuid('customer_id');
            $table->string('number_result');
            $table->unsignedBigInteger('invoice_number')->nullable()->unique();
            $table->string('payment_term')->nullable();
            $table->string('third_party_docs')->nullable();
            $table->date('date');
            $table->string('slug')->unique();
            $table->integer('tax')->nullable()->default(0);
            $table->integer('service_fee')->nullable()->default(0);
            $table->integer('discount')->nullable()->default(0);
            $table->integer('charges')->nullable()->default(0);
            $table->bigInteger('total')->nullable()->default(0);
            $table->uuid('contact_xero_id')->nullable();
            $table->uuid('invoice_xero_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('quote_id')->references('id')->on('quotes')->onDelete('cascade');
            $table->foreign('bast_id')->references('id')->on('basts')->onDelete('cascade');
            $table->foreign('user_created_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_updated_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
