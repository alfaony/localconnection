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
        Schema::create('used_laptop_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('used_laptop_id')->constrained()->onDelete('cascade');
            $table->foreignId('master_check_item_id')->constrained()->onDelete('cascade');
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('used_laptop_checks');
    }
};
