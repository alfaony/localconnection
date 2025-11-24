<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            // Remove old 'active' boolean column if exists
            // $table->dropColumn('active');
            
            // Add new status tracking columns
            $table->string('active_status', 20)->default('UNKNOWN')->after('ssl')
                ->index()
                ->comment('Current router status: UP, DOWN, ERROR, UNKNOWN');
                
            $table->timestamp('last_check_at')->nullable()->after('active_status')
                ->comment('Last health check timestamp');
                
            $table->string('last_error')->nullable()->after('last_check_at')
                ->comment('Last error message if status is ERROR');
                
            // Add index for common queries
            $table->index(['active_status', 'last_check_at'], 'router_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropIndex('router_status_idx');
            $table->dropColumn(['active_status', 'last_check_at', 'last_error']);
            
            // Restore old column if needed
            // $table->boolean('active')->default(false);
        });
    }
};