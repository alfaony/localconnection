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
       Schema::create('package_router_profiles', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->unsignedBigInteger('router_id');
            $t->unsignedBigInteger('package_id');
            $t->string('ros_profile')->nullable();     // PPP profile name
            $t->string('ros_queue_type_up')->nullable();   // PCQ up (untuk IPoE/Hotspot)
            $t->string('ros_queue_type_down')->nullable(); // PCQ down
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
            $t->foreign('package_id')->references('id')->on('internet_packages')->cascadeOnDelete();
            $t->unique(['router_id','package_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_router_profiles');
    }
};
