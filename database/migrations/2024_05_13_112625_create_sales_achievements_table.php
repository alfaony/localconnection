<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesAchievementsTable extends Migration
{
    public function up()
    {
        Schema::create('sales_achievements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('approval_user_id')->nullable(true);
            $table->string('slug')->unique();
            $table->string('period');  // e.g., "November 2024"
            $table->string('status');
            $table->decimal('sales_amount', 16, 2);  // Capaian Penjualan
            $table->integer('total_presentations')->nullable();  // Jumlah Presentasi
            $table->integer('total_offers_issued')->nullable();;  // Jumlah Penawaran Diterbitkan
            $table->integer('points')->nullable();  // Poin (akan diisi oleh manajemen)
            $table->boolean('approved')->default(false);  // Status persetujuan
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approval_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_achievements');
    }
}
