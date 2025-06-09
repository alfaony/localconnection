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
        Schema::create('potential_vendors', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->foreignId('item_request_id')->constrained('item_requests')->onDelete('cascade');
            $table->foreignId('product_supplier_id')->references('id')->on('product_suppliers')->onDelete('cascade');
            $table->boolean('responded')->default(false);
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('potential_vendors');
    }
};
