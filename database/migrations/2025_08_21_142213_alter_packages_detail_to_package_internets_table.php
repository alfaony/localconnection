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
        Schema::table('internet_packages', function (Blueprint $t) {
            $t->enum('access_type', ['pppoe','hotspot','ipoe'])
              ->default('pppoe')
              ->after('type');

            $t->unsignedInteger('rate_down_mbps')
              ->nullable()
              ->after('bandwidth');   // isi otomatis dari bandwidth kalau null
            $t->unsignedInteger('rate_up_mbps')
              ->nullable()
              ->after('rate_down_mbps');

            // FUP & kuota (opsional)
            $t->unsignedInteger('fup_rate_down_mbps')
              ->default(1)->after('rate_up_mbps');
            $t->unsignedInteger('fup_rate_up_mbps')
              ->default(1)->after('fup_rate_down_mbps');
            $t->unsignedBigInteger('quota_bytes')
              ->default(0)->after('fup_rate_up_mbps'); // 0 = unlimited

            // versioning & meta
            $t->unsignedInteger('version')
              ->default(1)->after('quota_bytes');
            $t->json('meta')->nullable()->after('version');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internet_packages', function (Blueprint $t) {
            $t->dropColumn([
                'access_type','rate_down_mbps','rate_up_mbps',
                'fup_rate_down_mbps','fup_rate_up_mbps',
                'quota_bytes','version','meta'
            ]);
        });
    }
};
