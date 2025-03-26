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
        Schema::create('agreement_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partnership_agreement_id')->constrained('partnership_agreements')->onDelete('cascade');
            $table->string('image_ktp');
            $table->string('signature');
            $table->integer('order')->default(1); // Urutan tanda tangan, 1 untuk TTD pertama, 2 untuk TTD kedua
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
        Schema::dropIfExists('agreement_signatures');
    }
};
