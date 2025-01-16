<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name');
            $table->text('location'); // Free text
            $table->decimal('longitude', 10, 7);
            $table->decimal('latitude', 10, 7);
            $table->uuid('warehouse_type_id'); // Foreign key
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('warehouse_type_id')->references('id')->on('warehouse_types')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('warehouses');
    }
};
