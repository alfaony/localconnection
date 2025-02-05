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
        Schema::table('import_progress', function (Blueprint $table) {
            $table->unsignedInteger('processed')->default(0)->change();
            $table->unsignedInteger('total_import')->default(0)->after('processed');
            $table->json('errors')->nullable()->after('total_import'); // Kolom untuk menyimpan error dalam format JSON
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_progress', function (Blueprint $table) {
            $table->string('processed')->change(); // Sesuaikan dengan tipe sebelumnya
            $table->dropColumn('errors'); // Hapus kolom errors jika ingin mengembalikan ke kondisi sebelumnya
            $table->dropColumn('total_import'); // Hapus kolom total_import jika ingin mengembalikan ke kondisi sebelumnya
        });
    }
};
