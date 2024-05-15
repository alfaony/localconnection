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
        Schema::create('ip_rights', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('approval_user_id')->nullable(true);
            $table->string('slug')->unique();
            $table->string('status');
            $table->string('name');
            $table->date('patent_date');
            $table->string('patent_number');
            $table->string('file_path');
            $table->text('description')->nullable();
            $table->string('point')->nullable(true);
            $table->boolean('approved')->default(false);  // Status persetujuan
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approval_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ip_rights');
    }
};
