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
        Schema::table('package_router_profiles', function (Blueprint $table) {
            $table->uuid('address_pool_id')->nullable()->after('ros_profile');
            $table->foreign('address_pool_id')->references('id')->on('address_pools')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('package_router_profiles', function (Blueprint $table) {
            $table->dropForeign(['address_pool_id']);
            $table->dropColumn('address_pool_id');
        });
    }
};
