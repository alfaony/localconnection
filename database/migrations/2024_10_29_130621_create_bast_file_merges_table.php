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
        Schema::create('bast_file_merges', function (Blueprint $table) {
            $table->id();
            $table->uuid('bast_id')->nullable();
            $table->integer('version')->nullable();
            $table->string('path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bast_id')->references('id')->on('basts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bast_file_merges');
    }
};
