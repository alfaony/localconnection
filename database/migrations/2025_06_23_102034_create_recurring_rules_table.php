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
        Schema::create('recurring_rules', function (Blueprint $table) {
            $table->id();
            $table->uuid('daily_task_id');
            $table->enum('frequency', ['DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY']);
            $table->integer('interval')->default(1); // Setiap 1 hari/minggu/bulan/tahun
            $table->json('by_day')->nullable(); // ['MO', 'WE', 'FR'] untuk WEEKLY
            $table->json('by_month_day')->nullable(); // [30] untuk tgl 30 tiap bulan
            $table->json('by_month')->nullable(); // [30] untuk tgl 30 tiap bulan
            $table->integer('count')->nullable(); // Berapa kali pengulangan
            $table->date('until')->nullable(); // Sampai tanggal berapa
            $table->date('start_date'); // Tanggal mulai
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('daily_task_id')->references('id')->on('daily_tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recurring_rules');
    }
};
