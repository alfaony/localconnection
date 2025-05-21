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
        Schema::create('item_purchases', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->foreignId('item_request_id')->constrained('item_requests')->onDelete('cascade');
            $table->uuid('sprinter_id'); // user_id yang assigned
            $table->decimal('actual_price', 12, 2);
            $table->string('bon_photo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('sprinter_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_purchases');
    }
};
