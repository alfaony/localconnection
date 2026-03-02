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
            $table->foreignUuid('user_customer_id')->nullable()->constrained('user_customers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('subdistrict_id')->constrained('subdistricts')->onDelete('cascade')->onUpdate('cascade');

            $table->foreignId('internet_package_id')->constrained('internet_packages')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('partnership_agreement_id')->nullable()->constrained('partnership_agreements')->onDelete('cascade')->onUpdate('cascade');

            $table->string('code')->nullable();
            $table->string('name');
            $table->text('address');
            $table->string('ktp_number')->nullable();
            $table->string('ktp_photo')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->string('status')->default('pending');
            
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
