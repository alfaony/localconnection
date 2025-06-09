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
        Schema::table('daily_tasks', function (Blueprint $table) {
            // Tambahkan kolom division_id
            $table->uuid('division_id')->nullable()->after('slug');
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');

            // Tambahkan kolom division_quota_lock_id
            $table->unsignedBigInteger('division_quota_lock_id')->nullable()->after('division_id');
            $table->foreign('division_quota_lock_id')
                ->references('id')
                ->on('division_quota_locks')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->dropForeign(['division_quota_lock_id']);
            $table->dropColumn('division_quota_lock_id');

            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
        });
    }
};
