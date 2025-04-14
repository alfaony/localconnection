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
        Schema::create('dayoffs', function (Blueprint $table) {
            $table->id();

            $table->uuid('user_id');
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->foreignId('dayoff_type_id')->constrained()->onDelete('cascade');
        
            $table->date('date_start');
            $table->date('date_end');
            $table->text('reason')->nullable();
            $table->text('reason_reject')->nullable();
            $table->string('file')->nullable();
        
            $table->uuid('approval_hr_user_id')->nullable()->index();
            $table->foreign('approval_hr_user_id')->references('id')->on('users')->nullOnDelete();
            $table->uuid('approval_finance_user_id')->nullable()->index();
            $table->foreign('approval_finance_user_id')->references('id')->on('users')->nullOnDelete();
        
            $table->timestamp('approved_hr_at')->nullable();
            $table->timestamp('approved_finance_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
        
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
        Schema::dropIfExists('dayoffs');
    }
};
