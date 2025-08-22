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
        Schema::create('address_pools', function (Blueprint $t) 
        {
            $t->uuid('id')->primary();
            $t->unsignedBigInteger('pop_id')->nullable();
            $t->string('name');
            $t->string('cidr', 64);   // ex: 10.10.10.0/24
            $t->string('gateway', 45)->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();
            $t->softDeletes();


            $t->foreign('pop_id')->references('id')->on('pops')->nullOnDelete();
            $t->unique(['name','cidr']);
            $t->index('pop_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('address_pools');
    }
};
