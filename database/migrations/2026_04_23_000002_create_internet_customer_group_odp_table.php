<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternetCustomerGroupOdpTable extends Migration
{
    public function up()
    {
        Schema::create('internet_customer_group_odp', function (Blueprint $table) {
            $table->string('group_id', 36);
            $table->unsignedBigInteger('optical_distribution_id');

            $table->primary(['group_id', 'optical_distribution_id']);

            $table->foreign('group_id')
                  ->references('id')
                  ->on('internet_customer_groups')
                  ->onDelete('cascade');

            $table->foreign('optical_distribution_id')
                  ->references('id')
                  ->on('optical_distributions')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('internet_customer_group_odp');
    }
}
