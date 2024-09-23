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
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class, 'user_id');
            $table->string('table_name')->nullable(); // Name of the associated table
            $table->string('table_id')->nullable();   // ID of the associated record
            $table->string('endpoint'); // The API endpoint called
            $table->string('method'); // The HTTP method used
            $table->json('request_payload')->nullable(); // Request data
            $table->json('response_payload')->nullable(); // Response data
            $table->integer('status_code')->nullable(); // HTTP status code
            $table->text('error_message')->nullable(); // Error message, if any
            $table->timestamp('executed_at')->useCurrent(); // Execution time
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
        Schema::dropIfExists('api_logs');
    }
};
