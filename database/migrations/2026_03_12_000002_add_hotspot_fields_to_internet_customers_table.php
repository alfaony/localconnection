<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internet_customers', function (Blueprint $table) {
            $table->foreignUuid('hotspot_server_id')
                ->nullable()
                ->after('router_id')
                ->constrained('hotspot_servers')
                ->nullOnDelete();

            // null = tidak ada binding
            // 'direct' = ip-binding langsung di MikroTik
            // 'radius' = via Framed-IP-Address di FreeRADIUS
            $table->string('ip_binding_type')
                ->nullable()
                ->after('hotspot_server_id');

            // null = tidak berlaku
            // 'regular' = login tetap tapi IP fixed
            // 'bypassed' = bypass login (MAC dikenali langsung)
            $table->string('ip_binding_mode')->default('regular')
                ->nullable()
                ->after('ip_binding_type');
        });
    }

    public function down(): void
    {
        Schema::table('internet_customers', function (Blueprint $table) {
            $table->dropForeign(['hotspot_server_id']);
            $table->dropColumn(['hotspot_server_id', 'ip_binding_type', 'ip_binding_mode']);
        });
    }
};
