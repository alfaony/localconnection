<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('approval_user_id')->nullable(true);
            $table->string('slug')->unique();
            $table->string('status');
            $table->string('name');
            $table->json('skills_mastered')->nullable(true);
            $table->date('certification_date');
            $table->string('certification_number');
            $table->string('certification_file')->nullable(true);
            $table->string('point')->nullable(true);
            $table->boolean('approved')->default(false);  // Status persetujuan
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approval_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('trainings');
    }
};
