<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('internet_customers', function (Blueprint $table) {
            $table->foreignUuid('group_id')
                  ->nullable()
                  ->after('grouping_id')
                  ->constrained('internet_customer_groups')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('internet_customers', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
    }
};
