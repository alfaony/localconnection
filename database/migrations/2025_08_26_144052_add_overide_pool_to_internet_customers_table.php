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
       Schema::table('internet_customers', function (Blueprint $t) {
            $t->uuid('override_pool_id')->nullable()->after('vlan_id');
            $t->foreign('override_pool_id')->references('id')->on('address_pools')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internet_customers', function (Blueprint $t) {
            $t->dropForeign(['override_pool_id']);
            $t->dropColumn('override_pool_id');
        }); 
    }
};
