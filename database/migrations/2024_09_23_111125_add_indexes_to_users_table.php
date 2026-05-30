
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Adding index to company_id in users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('company_id', 'users_company_id_index');
        });

        // Assuming there is a task_status_name column that is often queried
        // Schema::table('task_statuses', function (Blueprint $table) {
        //     $table->index('name', 'task_statuses_name_index');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Dropping index from company_id in users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_company_id_index');
        });

        // Dropping index from task_status_name in tasks table
        // Schema::table('task_statuses', function (Blueprint $table) {
        //     $table->dropIndex('task_statuses_name_index');
        // });
    }
};
