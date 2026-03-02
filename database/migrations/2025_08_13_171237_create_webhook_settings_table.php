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
        Schema::create('webhook_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('company_id')->constrained()->onDelete('cascade');
            $table->string('app_name');
            $table->json('selected_apps'); // Menyimpan array dari checkbox
            $table->string('url');
            $table->text('token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webhook_settings');
    }
};
