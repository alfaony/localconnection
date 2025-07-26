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
        Schema::create('data_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade');
            $table->integer('capacity_mb');
            $table->bigInteger('cost_per_month');
            $table->date('tanggal_tagihan');
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
        Schema::dropIfExists('data_centers');
    }
};
