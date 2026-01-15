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
        Schema::table('routers', function (Blueprint $t) {
            $t->uuid('default_pool_id')->nullable()->after('active');
            $t->foreign('default_pool_id')
                ->references('id')->on('address_pools')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('routers', function (Blueprint $t) {
            $t->dropForeign(['default_pool_id']);
            $t->dropColumn('default_pool_id');
        });
    }
};
