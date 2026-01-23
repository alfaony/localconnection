<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('partner_id');
            $table->year('year');
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');
            // $table->unique(['partner_id', 'year']);
            $table->index(['year', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_targets');
    }
};