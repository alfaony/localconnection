<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('pop_id')->nullable()->constrained('pops')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();

            $table->string('name');
            $table->string('vendor', 50)->default('hioso');
            $table->string('model')->nullable();
            $table->string('host');
            $table->unsignedSmallInteger('port')->default(161);

            $table->string('snmp_version', 10)->default('2c');
            $table->text('snmp_community')->nullable();
            $table->string('snmp_username')->nullable();
            $table->string('snmp_security_level', 20)->default('authPriv');
            $table->string('snmp_auth_protocol', 10)->nullable();
            $table->text('snmp_auth_password')->nullable();
            $table->string('snmp_priv_protocol', 10)->nullable();
            $table->text('snmp_priv_password')->nullable();

            $table->json('oid_map')->nullable();
            $table->boolean('polling_enabled')->default(true);
            $table->unsignedSmallInteger('polling_interval')->default(5);

            $table->string('status', 20)->default('UNKNOWN')->index();
            $table->text('system_description')->nullable();
            $table->unsignedBigInteger('uptime_seconds')->nullable();
            $table->timestamp('last_polled_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->text('last_error')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'host']);
            $table->index(['polling_enabled', 'last_polled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olts');
    }
};
