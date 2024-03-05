<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(FieldSettingCompaniesSeeder::class);
        $this->call(PermissionForProductTillSettingCompanySeeder::class);
        $this->call(AllUserIsAdminSeeder::class);
        $this->call(PermissionForProductPriceQuoteAndWorkOrderSeeder::class);
        $this->call(PermissionForDeleteDetailReportSeeder::class);
        $this->call(PermissionForProductPriceSuplierSeeder::class);
        $this->call(PermissionPricelistSeeder::class);
        $this->call(PermissionForCompanySeeder::class);
        $this->call(PermissionSuggestionWorkOrderSeeder::class);
        $this->call(PermissionQuoteShowSeeder::class);
        $this->call(PermissionWorkOrderShowSeeder::class);
        $this->call(SmtpSettingSeeder::class);
    }
}
