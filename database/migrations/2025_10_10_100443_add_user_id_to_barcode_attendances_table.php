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
        Schema::table('barcode_attendances', function (Blueprint $table) {
            $table->foreignUuid('user_create_id')->nullable()->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('barcode_attendances', function (Blueprint $table) {
            $table->dropForeign(['user_create_id']);
            $table->dropColumn('user_create_id');
        });
    }
};
