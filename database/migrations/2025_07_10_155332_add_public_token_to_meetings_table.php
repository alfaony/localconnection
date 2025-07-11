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
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('public_token')->nullable()->after('attachment_link');
            $table->string('public_code')->nullable()->after('public_token');
            $table->timestamp('public_token_generated_at')->nullable()->after('public_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('public_token');
            $table->dropColumn('public_code');
            $table->dropColumn('public_token_generated_at');
        });
    }
};
