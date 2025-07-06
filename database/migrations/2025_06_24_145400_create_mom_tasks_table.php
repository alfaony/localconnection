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
        Schema::create('mom_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')->constrained('mom_agendas')->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('attachment')->nullable();
            $table->uuid('task_status_id')->nullable()->constrained('task_statuses')->nullOnDelete();

            // Assignment
            $table->string('external_email')->nullable(); // eksternal
            $table->uuid('token')->nullable()->unique(); // akses eksternal
            
            // Sinkronisasi ke daily task
            $table->uuid('daily_task_id')->nullable()->constrained('daily_tasks')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mom_tasks');
    }
};
