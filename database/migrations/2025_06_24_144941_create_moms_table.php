
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
        Schema::create('moms', function (Blueprint $table) 
        {
            $table->id();
            $table->uuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('meeting_id')->nullable()->constrained('meetings')->nullOnDelete();
            $table->uuid('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->date('mom_date');
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
        Schema::dropIfExists('moms');
    }
};
