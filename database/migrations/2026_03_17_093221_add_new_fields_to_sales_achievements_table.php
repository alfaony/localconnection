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
        Schema::table('sales_achievements', function (Blueprint $table) {
            $table->integer('customer_visits')->nullable()->comment('Jumlah Kunjungan Pelanggan');
            $table->integer('registered_customers')->nullable()->comment('Jumlah Pelanggan Daftar');
            $table->integer('active_customers')->nullable()->comment('Jumlah Pelanggan Aktif');
            $table->datetime('attempt_point_date')->nullable()->comment('Kapan tanggal point dilakukan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_achievements', function (Blueprint $table) {
            $table->dropColumn([
                'customer_visits',
                'registered_customers',
                'active_customers',
                'attempt_point_date'
            ]);
        });
    }
};
