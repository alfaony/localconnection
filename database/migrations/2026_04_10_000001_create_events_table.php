<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();        // S3 path, tampil di show saja
            $table->string('color')->default('#667eea'); // warna bar kalender
            $table->date('start_date');                 // tanggal mulai occurrence pertama
            $table->date('end_date');                   // tanggal selesai occurrence pertama
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_routine')->default(false);      // repeat tiap minggu?
            $table->date('routine_end_date')->nullable();       // batas akhir routine (null = selamanya)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
