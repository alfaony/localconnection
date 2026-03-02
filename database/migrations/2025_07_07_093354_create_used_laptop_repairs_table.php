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
        Schema::create('used_laptop_repairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('used_laptop_id')->constrained()->onDelete('cascade');
            $table->string('repair_item'); // input bebas
            $table->unsignedBigInteger('cost')->default(0);
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
        Schema::dropIfExists('used_laptop_repairs');
    }
};
