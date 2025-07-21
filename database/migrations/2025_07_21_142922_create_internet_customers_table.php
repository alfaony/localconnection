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
        Schema::create('internet_customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained();
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('subdistrict_id')->constrained('subdistricts')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignUuid('technical_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('internet_package_id')->constrained('internet_packages')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('partnership_agreement_id')->constrained('partnership_agreements')->onDelete('cascade')->onUpdate('cascade');
            $table->string('xendit_invoice_id')->nullable(); // ID dari invoice Xendit
            $table->string('xendit_payment_method')->nullable(); // e.g. 'QRIS', 'OVO', 'BCA'
            $table->timestamp('xendit_paid_at')->nullable(); // waktu pembayaran
            $table->json('xendit_raw_response')->nullable(); // menyimpan response lengkap dari Xendit (opsional)
            $table->string('name');
            $table->text('address');
            $table->string('payment_method')->nullable();
            $table->string('payment_proof')->nullable();
            $table->string('ktp_number')->nullable();
            $table->string('ktp_photo')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->string('status')->default('pending');
            $table->decimal('amount_paid', 15, 2);
            $table->string('device_serial_number')->nullable();
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
        Schema::dropIfExists('internet_customers');
    }
};
