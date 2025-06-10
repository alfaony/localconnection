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
        Schema::create('item_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->uuid('user_id'); // requester
            $table->foreignId('supplier_category_id')->constrained('supplier_categories')->onDelete('cascade');
            $table->timestamp('date')->nullable();
            $table->string('picture')->nullable(); // Path foto barang pendukung
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->bigInteger('estimated_price');
            $table->integer('qty')->nullable();
            $table->uuid('assigned_pic_id')->nullable(); // sprinter
            $table->string('status'); // e.g. requested, waiting_payment, paid, done
            $table->boolean('is_open')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_pic_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_requests');
    }
};
