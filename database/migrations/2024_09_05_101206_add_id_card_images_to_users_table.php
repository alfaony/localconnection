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
        Schema::table('users', function (Blueprint $table) {
            $table->text('id_card_image')->nullable()->after('id_card'); // Menambahkan kolom id_card_images
            $table->string('npwp_number')->nullable()->after('id_card_image'); // Menambahkan kolom id_card_images
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id_card_image'); // Menghapus kolom id_card_images
            $table->dropColumn('npwp_number'); // Menghapus kolom npwp_number
        });
    }
};
