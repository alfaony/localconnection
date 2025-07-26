
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
        Schema::create('internet_customer_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('internet_customer_id')->constrained('internet_customers')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignUuid('user_finance_id')->nullable()->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('confirmation_finance_at')->nullable();
            $table->decimal('amount_paid', 15, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_proof')->nullable();
            $table->string('xendit_invoice_id')->nullable(); // ID dari invoice Xendit
            $table->string('xendit_payment_method')->nullable(); // e.g. 'QRIS', 'OVO', 'BCA'
            $table->timestamp('xendit_paid_at')->nullable(); // waktu pembayaran
            $table->json('xendit_raw_response')->nullable(); // menyimpan response lengkap dari Xendit (opsional)
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
        Schema::dropIfExists('internet_customer_purchases');
    }
};
