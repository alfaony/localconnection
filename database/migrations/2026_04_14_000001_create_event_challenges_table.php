<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot table: satu event bisa punya banyak challenge, challenge tidak wajib punya event
        Schema::create('event_challenges', function (Blueprint $table) {
            $table->uuid('event_id');
            $table->uuid('challenge_id');
            $table->timestamps();

            $table->primary(['event_id', 'challenge_id']);
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            $table->foreign('challenge_id')->references('id')->on('challenges')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_challenges');
    }
};
