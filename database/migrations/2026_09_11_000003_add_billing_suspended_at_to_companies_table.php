<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            // NULL = akses normal. Diisi begitu tagihan platform lewat due_date
            // tanpa dibayar; dikosongkan lagi otomatis begitu invoice yang
            // menunggak dibayar (lihat PlatformInvoiceService::markPaid()).
            $table->timestamp('billing_suspended_at')->nullable()->after('custom_domain_verified_at');
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('billing_suspended_at');
        });
    }
};
