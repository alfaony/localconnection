<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoftwarePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('software_packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('software_id');
            $table->string('nama_paket');
            $table->integer('durasi_hari');
            $table->decimal('harga', 15, 2);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('software_id')->references('id')->on('softwares')->onDelete('cascade');
            
            // Indexes
            $table->index(['software_id', 'status'], 'idx_software_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('software_packages');
    }
}