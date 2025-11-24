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
       Schema::create('pppoe_servers', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->unsignedBigInteger('router_id');
            $t->uuid('interface_id');       // interface akses
            $t->string('service_name')->nullable(); // PPPoE service-name
            $t->uuid('address_pool_id')->nullable();
            $t->boolean('only_one')->default(true); // map to only-one=yes
            $t->json('meta')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
            $t->foreign('interface_id')->references('id')->on('router_interfaces')->cascadeOnDelete();
            $t->foreign('address_pool_id')->references('id')->on('address_pools')->nullOnDelete();

            $t->unique(['router_id','interface_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pppoe_servers');
    }
};
