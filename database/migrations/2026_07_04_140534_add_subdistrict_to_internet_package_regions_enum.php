<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE internet_package_regions MODIFY COLUMN region_type ENUM('province', 'city', 'district', 'subdistrict') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE internet_package_regions MODIFY COLUMN region_type ENUM('province', 'city', 'district') NOT NULL");
    }
};
