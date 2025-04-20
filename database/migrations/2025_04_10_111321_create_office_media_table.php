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
        Schema::create('office_media', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->uuid('user_id');
            $table->enum('type', ['image', 'youtube']);
            $table->string('title')->nullable();
            $table->string('file_path')->nullable(); // for image
            $table->string('youtube_url')->nullable(); // for youtube link
            $table->boolean('is_temporary')->default(true);
            $table->timestamps();
            $table->softDeletes(); // in case you want to archive before real delete

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('office_media');
    }
};
