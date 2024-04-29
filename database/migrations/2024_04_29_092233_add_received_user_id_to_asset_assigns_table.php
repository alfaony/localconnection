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
        Schema::table('asset_assigns', function (Blueprint $table) {
            $table->uuid('received_to_user_id')->nullable(true)->after('assigned_to_user_id');
            $table->foreign('received_to_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_assigns', function (Blueprint $table) {
            $table->dropForeign('asset_assigns_received_to_user_id_foreign');
            $table->dropColumn('received_to_user_id');
        });
    }
};
