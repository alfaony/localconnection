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
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('who')->nullable(); // user/email/service
            $t->string('action');          // e.g., provision, suspend, migrate
            $t->unsignedBigInteger('router_id')->nullable();
            $t->uuid('subscriber_id')->nullable();
            $t->json('before')->nullable();
            $t->json('after')->nullable();
            $t->timestamp('created_at')->useCurrent();

            $t->index(['router_id','subscriber_id']);
            $t->index(['action','created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
