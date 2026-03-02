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
        Schema::create('internet_customer_installations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('internet_customer_id')->constrained('internet_customers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignUuid('technical_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('device_serial_number')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->string('photos')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('internet_customer_installations');
    }
};
