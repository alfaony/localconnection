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
        Schema::create('dayoff_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Cuti Biasa, Cuti Sakit
            $table->boolean('is_limited')->default(true); // true = pakai kuota
            $table->integer('default_quota')->nullable(); // null jika unlimited
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
        Schema::dropIfExists('dayoff_types');
    }
};
