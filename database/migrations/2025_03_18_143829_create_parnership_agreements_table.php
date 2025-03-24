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
        Schema::create('partnership_agreements', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id');
            $table->foreignUuid('user_created_id')->constrained('users');
            $table->foreignUuid('user_updated_id')->nullable()->constrained('users');
            $table->foreignId('partnership_agreement_type_id')->constrained();
            $table->bigInteger('letter_number');
            $table->string('number_result');
            $table->date('date_agreement');    
            $table->string('status')->default('draf');
            $table->boolean('is_approve')->nullable()->default(null);
            $table->text('reason')->nullable();
            $table->json('fields')->nullable();
            $table->boolean('is_password')->default(false);
            $table->string('password')->nullable();
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
        Schema::dropIfExists('partnership_agreements');
    }
};
