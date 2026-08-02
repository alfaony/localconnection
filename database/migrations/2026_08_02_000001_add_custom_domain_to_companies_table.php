<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            // Domain custom yang dipakai company (mis: "www.domainsaya.com").
            // NULL selama company masih pakai subdomain gratis (slug.internetrt.com)
            $table->string('custom_domain')->nullable()->unique()->after('slug');

            // Token buat verifikasi kepemilikan domain (TXT record).
            // Di-generate sekali pas company nge-submit custom domain baru.
            $table->string('custom_domain_verification_token')->nullable()->after('custom_domain');

            // NULL = belum diverifikasi (custom domain belum aktif dipakai,
            // sistem masih fallback ke subdomain). Diisi begitu verifikasi
            // TXT record berhasil.
            $table->timestamp('custom_domain_verified_at')->nullable()->after('custom_domain_verification_token');
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['custom_domain', 'custom_domain_verification_token', 'custom_domain_verified_at']);
        });
    }
};
