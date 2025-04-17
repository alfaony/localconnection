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
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->uuid('division_id');
            $table->date('date'); // Tanggal submit
            $table->year('year');
            $table->integer('week'); // Auto-increment 1–52 per tahun dan per divisi
            $table->string('file')->nullable();
            
        
            // Text fields
            $table->text('key_activities')->nullable();
            $table->text('problems')->nullable();
            $table->text('targets')->nullable();
        
            // Numeric fields
            $table->integer('number_of_customers')->nullable();
            $table->integer('number_of_users')->nullable();
            $table->integer('number_of_products')->nullable();
            $table->integer('number_of_projects')->nullable();
            $table->integer('number_of_homepasses')->nullable();
            $table->integer('number_of_leads')->nullable();
            $table->integer('number_of_views')->nullable();
            $table->integer('number_of_profit')->nullable();
        
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('weekly_reports');
    }
};
