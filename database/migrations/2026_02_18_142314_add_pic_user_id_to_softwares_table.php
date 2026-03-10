<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPicUserIdToSoftwaresTable extends Migration
{
    public function up()
    {
        Schema::table('softwares', function (Blueprint $table) {
            $table->uuid('pic_user_id')->nullable()->after('status');
            $table->foreign('pic_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('softwares', function (Blueprint $table) {
            $table->dropForeign(['pic_user_id']);
            $table->dropColumn('pic_user_id');
        });
    }
}
