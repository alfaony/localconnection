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
        Schema::create('direct_points', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('from_user_id');
            $table->uuid('to_user_id');
            $table->uuid('division_id');
            $table->foreignId('division_quota_lock_id')->nullable()->constrained('division_quota_locks')->nullOnDelete();
            $table->integer('point');
            $table->integer('approved_point')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->default('pending');
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('status');
            $table->index('created_at');
            $table->index(['division_id', 'division_quota_lock_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('direct_points');
    }
};
