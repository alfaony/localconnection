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
        
        Schema::create('flow_charts', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->uuid('user_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->longText('json_model')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // Optional
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
        Schema::dropIfExists('flow_charts');
    }
};
