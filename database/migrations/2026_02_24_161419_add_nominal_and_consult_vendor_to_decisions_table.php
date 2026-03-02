<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->decimal('nominal', 18, 2)->nullable()->after('execution_score');
            $table->string('consult_vendor')->nullable()->after('nominal');
        });
    }

    public function down()
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropColumn(['nominal', 'consult_vendor']);
        });
    }
};
