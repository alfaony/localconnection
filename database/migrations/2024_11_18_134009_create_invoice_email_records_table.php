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
        Schema::create('invoice_email_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('invoice_id');
            $table->uuid('user_id')->nullable();
            $table->json('to'); // Store as JSON
            $table->json('cc')->nullable(); // Store as JSON
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_email_records');
    }
};
