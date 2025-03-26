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
        Schema::table('partnership_agreements', function (Blueprint $table) {
            $table->uuid('token')->nullable()->after('password');
            $table->boolean('is_share')->default(false)->after('token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partnership_agreements', function (Blueprint $table) {
            $table->dropColumn(['token', 'is_share']);
        });
    }
};
