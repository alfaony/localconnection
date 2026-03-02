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
        Schema::create('usage_daily', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('internet_customer_id');
            $t->date('date');
            $t->unsignedBigInteger('up_bytes')->default(0);
            $t->unsignedBigInteger('down_bytes')->default(0);
            $t->enum('source', ['queue','netflow','snmp'])->default('queue');
            $t->timestamps();
            $t->softDeletes();

            $t->foreign('internet_customer_id')->references('id')->on('internet_customers')->cascadeOnDelete();
            $t->unique(['internet_customer_id','date']);
            $t->index(['date','source']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usage_daily');
    }
};
