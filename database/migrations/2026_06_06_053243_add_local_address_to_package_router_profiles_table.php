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
            $table->string('local_address', 45)->nullable()->after('address_pool_id');
        });
    }

    public function down()
    {
        Schema::table('package_router_profiles', function (Blueprint $table) {
            $table->dropColumn('local_address');
        });
    }
};
