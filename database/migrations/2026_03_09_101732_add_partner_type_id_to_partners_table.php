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
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('partner_type')->nullable()->change();
            $table->foreignUuid('partner_type_id')->nullable()->after('name')->constrained('partner_types')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropForeign(['partner_type_id']);
            $table->dropColumn('partner_type_id');
            $table->string('partner_type')->nullable(false)->change();
        });
    }
};
