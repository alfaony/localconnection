<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('network_incidents', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->uuid('internet_customer_id');
            $table->foreignId('router_id')->nullable()->constrained('routers')->onDelete('set null');

            // 'mass_outage' kalau ke-grouping bareng banyak customer lain di
            // router yang sama, 'single' kalau cuma customer ini sendiri
            $table->string('type')->default('single');
            $table->unsignedInteger('affected_count')->default(1); // total customer dalam grup insiden ini

            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();

            $table->timestamp('noc_notified_at')->nullable();
            $table->timestamp('customer_notified_at')->nullable();

            $table->text('noc_wa_response')->nullable();
            $table->text('customer_wa_response')->nullable();

            $table->timestamps();

            $table->index(['internet_customer_id', 'resolved_at']);
            $table->index(['router_id', 'detected_at']);

            $table->foreign('internet_customer_id')->references('id')->on('internet_customers')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('network_incidents');
    }
};
