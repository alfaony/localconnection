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
        Schema::create('letter_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Primary key
            $table->uuid('letter_type_id'); // Foreign key to letter_types
            $table->uuid('user_id'); // Foreign key to users table, assuming a user submits a letter
            $table->boolean('is_approved')->nullable(); // Boolean status, nullable (true = approved, false = rejected, null = pending)
            $table->json('field')->nullable(); // JSON column for additional data
            $table->timestamps(); // Timestamps for created_at and updated_at
            $table->softDeletes(); // Soft delete functionality
        
            // Foreign key constraints
            $table->foreign('letter_type_id')->references('id')->on('letter_types')->onDelete('cascade');
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
        Schema::dropIfExists('letter_submissions');
    }
};
