<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingRecurrencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meeting_recurrences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id');
            $table->string('recurring_type'); // 'daily', 'monthly', 'yearly'
            $table->json('recurring_daily_days')->nullable(); // ['Monday', 'Tuesday']
            $table->integer('recurring_monthly_date')->nullable(); // 1 - 31
            $table->integer('recurring_yearly_month')->nullable(); // 1 - 12
            $table->integer('recurring_yearly_date')->nullable(); // 1 - 31
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meeting_recurrences');
    }
}
