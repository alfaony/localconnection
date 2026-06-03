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
        Schema::create('internet_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('company_id', 36)->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->string('name');
            $table->enum('category', [
                'router', 'switch', 'odp', 'onu', 'cable',
                'server', 'tower', 'antenna', 'splitter', 'other'
            ])->default('other');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->date('purchase_date');
            $table->string('vendor')->nullable();
            $table->unsignedSmallInteger('warranty_months')->default(0);
            $table->enum('status', ['active', 'damaged', 'maintenance', 'sold'])->default('active');
            $table->date('damaged_at')->nullable();
            $table->date('sold_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index('purchase_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internet_assets');
    }
};
