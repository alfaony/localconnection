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
        Schema::create('quotes', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID sebagai primary key
            $table->uuid('user_created_id');
            $table->uuid('user_updated_id');
            $table->uuid('customer_id');
            $table->date('date');
            $table->string('slug')->unique();
            $table->integer('tax')->nullable()->default(0);
            $table->integer('service_fee')->nullable()->default(0);
            $table->integer('discount')->nullable()->default(0);
            $table->integer('charges')->nullable()->default(0);
            $table->bigInteger('total')->nullable()->default(0);

            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('quote');
    }
};
