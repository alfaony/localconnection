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
            $table->text('background')->nullable()->after('delete_able');
            $table->text('experience')->nullable()->after('background');
            $table->text('skill')->nullable()->after('experience');
            $table->json('achievement')->nullable()->after('skill');
            $table->json('failure')->nullable()->after('achievement');            
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
            $table->dropColumn(['background', 'experience', 'skill', 'achievement', 'failure']);
            
        });
    }
};
