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
        Schema::table('agreement_letters', function (Blueprint $table) {
            $table->unsignedBigInteger('template_agreement_id')->nullable()->after('quote_id');
            $table->json('custom_fields')->nullable()->after('slug');

            $table->foreign('template_agreement_id')->references('id')->on('template_agreements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agreement_letters', function (Blueprint $table) {
            $table->dropForeign('agreement_letters_template_agreement_id_foreign');
            $table->dropColumn('template_agreement_id');
            $table->dropColumn('custom_fields');
        });
    }
};
