<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wablas_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source')->index();        // e.g. internet_customer, billing_reminder
            $table->uuid('source_id')->nullable()->index(); // UUID dari record sumber
            $table->string('phone');
            $table->text('message');
            $table->string('type')->default('text');  // text, image, video, document, audio
            $table->string('status')->default('pending'); // pending, success, failed
            $table->json('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wablas_logs');
    }
};
