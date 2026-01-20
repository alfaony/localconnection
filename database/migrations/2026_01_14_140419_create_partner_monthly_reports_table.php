<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_monthly_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('partner_target_value_id');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->year('year');
            $table->tinyInteger('month');
            $table->bigInteger('achievement_value')->default(0);
            $table->decimal('achievement_percentage', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->uuid('reported_by');
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('partner_target_value_id', 'fk_monthly_report_target_value')
                  ->references('id')
                  ->on('partner_target_values')
                  ->onDelete('cascade');
            
            // $table->unique(['partner_target_value_id', 'year', 'month'], 'unique_monthly_report');
            $table->index(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_monthly_reports');
    }
};