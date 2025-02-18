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
        Schema::create('fine_tune_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fine_tune_table_id')->constrained('fine_tune_tables')->onDelete('cascade');
            $table->foreignId('fine_tune_id')->constrained('fine_tunes')->onDelete('cascade');
            $table->uuid('company_id')->nullable(true);
            $table->string('filename')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fine_tune_files');
    }
};
