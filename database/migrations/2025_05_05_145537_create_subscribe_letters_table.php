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
        Schema::create('subscribe_letters', function (Blueprint $table) {
            $table->id();
            $table->uuid('pic_user_id');
            $table->uuid('company_id');
            $table->string('name');
            $table->date('valid_from');
            $table->date('valid_until');
            $table->string('document_path')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pic_user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('subscribe_letters');
    }
};
