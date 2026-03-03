<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('software_id');
            $table->string('nama_akun');
            $table->integer('max_slots')->default(5);
            $table->integer('used_slots')->default(0);
            
            // Flexible Access Fields
            $table->text('email_akun')->nullable();
            $table->text('password_akun')->nullable();
            $table->string('pin_code')->nullable();
            $table->text('link_invite')->nullable();
            $table->longText('instruksi_akses')->nullable();
            $table->string('attachment')->nullable();
            
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('software_id')->references('id')->on('softwares')->onDelete('cascade');
            
            // Indexes
            $table->index(['company_id', 'software_id'], 'idx_company_software');
            $table->index('status', 'idx_status');
            $table->index(['max_slots', 'used_slots', 'status'], 'idx_slots');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_accounts');
    }
}