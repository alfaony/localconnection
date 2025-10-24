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
        Schema::create('internet_installation_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internet_installation_id')->constrained('internet_customer_installations')->onDelete('cascade')->onUpdate('cascade');
            $table->string('photo');
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internet_installation_photos');
    }
};
