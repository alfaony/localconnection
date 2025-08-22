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
        Schema::create('router_interfaces', function (Blueprint $t) 
        {
            $t->uuid('id')->primary();
            $t->unsignedBigInteger('router_id');
            $t->string('name'); // ether1, vlan300, etc
            $t->enum('role', ['uplink','access','management'])->default('access');
            $t->unsignedInteger('vlan_id')->nullable();
            $t->uuid('address_pool_id')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
            $t->foreign('address_pool_id')->references('id')->on('address_pools')->nullOnDelete();
            $t->unique(['router_id','name']);
            $t->index(['role','vlan_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('router_interfaces');
    }
};
