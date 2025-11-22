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
        Schema::table('internet_customers', function (Blueprint $table) {
            $table->foreignId('optical_distribution_id')->nullable()->constrained('optical_distributions')->after('subdistrict_id')->onDelete('cascade');
            $table->string('grouping_id')->nullable()->after('optical_distribution_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internet_customers', function (Blueprint $table) {
            $table->dropForeign(['optical_distribution_id']);
            $table->dropColumn(['optical_distribution_id', 'grouping_id']);
        });
    }
};
