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
        Schema::create('migration_used_laptop_checks', function (Blueprint $table) {
             $table->id();
            $table->foreignId('used_laptop_id')->constrained()->onDelete('cascade');
            $table->string('check_item');
            $table->enum('status', ['baik', 'rusak', 'tidak dicek'])->default('tidak dicek');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('migration_used_laptop_checks');
    }
};
