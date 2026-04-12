<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('created_by');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('reward_point')->default(0);
            $table->unsignedInteger('reward_xp')->default(0);
            $table->string('module_type'); // task|internet|kasir|sprinter|meeting|decision|weekly_report|score
            $table->unsignedInteger('target_count');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['company_id', 'start_date', 'end_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('challenges');
    }
};
