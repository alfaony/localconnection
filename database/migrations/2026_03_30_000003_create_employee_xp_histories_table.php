<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_xp_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('company_id');
            $table->integer('xp');                      // bisa negatif untuk penalty
            $table->string('source_type');              // nama class model
            $table->string('source_id')->nullable();    // ID record sumber
            $table->string('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // Sengaja tidak ada updated_at — history bersifat immutable

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->index(['user_id', 'created_at']);
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_xp_histories');
    }
};
