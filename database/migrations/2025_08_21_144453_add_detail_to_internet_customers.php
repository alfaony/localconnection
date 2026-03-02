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
        Schema::table('internet_customers', function (Blueprint $table) 
        {
            // --- kolom teknis AAA / Mikrotik ---
            $table->foreignId('router_id')->nullable()->constrained('routers')->nullOnDelete();
            $table->enum('access_type', ['pppoe','hotspot','ipoe'])->default('pppoe');
            $table->string('username')->nullable();   // PPPoE/Hotspot username
            $table->string('pass_hash')->nullable();            // hash password
            $table->string('ip_address', 45)->nullable();
            $table->string('mac_address', 32)->nullable();
            $table->unsignedInteger('vlan_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('ros_comment_uuid', 64)->nullable(); // untuk tag di Mikrotik
            $table->json('meta')->nullable();

            $table->index(['router_id','status','access_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('internet_customers', function (Blueprint $table) {
            // hapus foreign key dulu
            $table->dropForeign(['router_id']);

            // lalu drop kolom2 yang ditambahkan
            $table->dropColumn([
                'router_id',
                'access_type',
                'username',
                'pass_hash',
                'ip_address',
                'mac_address',
                'vlan_id',
                'expires_at',
                'ros_comment_uuid',
                'meta',
            ]);

            // drop index gabungan
            $table->dropIndex(['router_id','status','access_type']);
        });
    }
};
