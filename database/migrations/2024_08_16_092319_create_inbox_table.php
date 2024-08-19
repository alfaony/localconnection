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
        Schema::create('inboxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id_to');
            $table->uuid('user_id_from');
            $table->text('message');
            $table->string('direct_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
    
            // Foreign keys
            $table->foreign('user_id_to')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id_from')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inboxes');
    }
};
