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
        Schema::create('product_store_media', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('product_store_id')->constrained('product_stores')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('order')->default(0);
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_store_media');
    }
};
