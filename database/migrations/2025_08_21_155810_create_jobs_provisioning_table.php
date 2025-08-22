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
        Schema::create('jobs_provisioning', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->enum('type', ['provision','suspend','unsuspend','migrate','reconcile']);
            $t->uuid('internet_customer_id')->nullable();
            $t->unsignedBigInteger('router_id')->nullable();
            $t->enum('status', ['queued','running','succeeded','failed'])->default('queued');
            $t->unsignedTinyInteger('attempts')->default(0);
            $t->text('last_error')->nullable();
            $t->json('payload')->nullable(); // tambahan data
            $t->timestamps();
            $t->softDeletes();

            $t->foreign('internet_customer_id')->references('id')->on('internet_customers')->nullOnDelete();
            $t->foreign('router_id')->references('id')->on('routers')->nullOnDelete();
            $t->index(['type','status']);
            $t->index(['router_id','status']);
            $t->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs_provisioning');
    }
};
