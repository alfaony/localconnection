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
        $this->call(CallMenuEqipmentSeeder::class);
        $this->call(CallMenuTaskSeeder::class);
        $this->call(CallMenuAssetSeeder::class);
        $this->call(CallMenuAttendanceSeeder::class);
        $this->call(PermissionForMenuSecurity::class);
        $this->call(PermissionForTicketSeeder::class);
        $this->call(CallMenuProductivity::class);
        $this->call(PermissionForMenuUserProfile::class);
        $this->call(PermissionForMenuDailyTaskCategory::class);
        $this->call(CallMenuShiftingob::class);
        $this->call(PermissionForMenuDivisionBudget::class);
        $this->call(PermissionForDivisionFatching::class);
        $this->call(PermissionForCreateDailyTaskOnProject::class);
        $this->call(PermissionForDailyTaskImportImportSeeder::class);
        $this->call(PermissionForMenuProductCategory::class);
        $this->call(PermissionForMenuAttendanceSeeder::class);
        $this->call(PermissionForMenuDailyTaskProjectKanbanSeeder::class);
        $this->call(PermissionForExportDailyTask::class);
        $this->call(PermissionForMenuInbox::class);
        $this->call(PermissionForMenuMonitoringBast::class);
        $this->call(PermissionAndSettingMenuLetterTypeSeeder::class);
        $this->call(PermissionForMenuPosition::class);
        $this->call(PermissionForMenuDevice::class);
        $this->call(PermissionForMenuXero::class);
        $this->call(PermissionForMenuInvoice::class);
        $this->call(PermissionForProjectExportSeeder::class);
        $this->call(PermissionForRequestProjectSeeder::class);
        $this->call(PermissionForDownloadAllReportSeeder::class);
        $this->call(PermissionForHistoryInvoiceSeeder::class);
        $this->call(PermissionForSuggestWorkOrderSeeder::class);
        $this->call(PermissionForMenuReportProjectApprovealSeeder::class);
    }
}
