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
        Schema::create('used_laptops', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->uuid('company_id');
            $table->boolean('is_sold')->default(false); // Sudah terjual atau belum
            $table->unsignedBigInteger('sold_price')->nullable(); // Harga jual real
            $table->date('sold_at')->nullable(); // Tanggal jual
            $table->string('name');
            $table->string('processor')->nullable();
            $table->string('ram')->nullable();
            $table->string('ssd')->nullable();
            $table->string('gpu')->nullable();
            $table->string('operating_system')->nullable();
            $table->unsignedBigInteger('purchase_price')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('used_laptops');
    }
};
