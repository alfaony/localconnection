<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_store_id')->unique()->constrained('product_stores')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->string('unit')->default('pcs');
            $table->string('external_id')->nullable()->comment('ID produk di in-house inventory software');
            $table->timestamp('last_sync_at')->nullable();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignUuid('user_create_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('user_modified_id')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories');
    }
};
