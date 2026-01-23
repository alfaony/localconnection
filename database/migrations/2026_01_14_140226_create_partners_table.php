<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignUuid('pic_user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name');
            $table->string('partner_type');
            $table->string('industry')->nullable();
            $table->string('website')->nullable();
            $table->string('status')->nullable();
            $table->boolean('is_certified')->default(false);
            $table->string('certification_level')->nullable();
            $table->string('certification_file')->nullable();
            $table->date('certified_at')->nullable();
            $table->date('partnership_started_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index('pic_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};