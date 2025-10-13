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
        Schema::table('used_laptops', function (Blueprint $table) {
            $table->foreignUuid('rack_id')->nullable()->after('id')->constrained('racks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('used_laptops', function (Blueprint $table) {
            $table->dropForeign(['rack_id']);
            $table->dropColumn('rack_id');
        });
    }
};
