<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('company_customer_snapshots', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->date('snapshot_date');

            // Jumlah InternetCustomer berstatus installed/reactivated pada company
            // ini, per hari. Dipakai buat hitung tagihan bulanan (rata-rata harian),
            // bukan cuma angka di akhir bulan, biar gak gampang diakalin.
            $table->unsignedInteger('active_customer_count')->default(0);

            $table->timestamps();

            $table->unique(['company_id', 'snapshot_date']);
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_customer_snapshots');
    }
};
