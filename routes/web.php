<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingCompanyController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\SubdistrictController;
use App\Http\Controllers\PostalCodeController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\WablasWebhookController;
use App\Http\Controllers\BroadcastAuthController;
use App\Http\Controllers\XenditController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\InternetCustomerController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;

// LiveWire
use App\Http\Livewire\DataCenter\Index;
use App\Http\Livewire\DataCenter\Form;
use App\Http\Livewire\Ods\OdsIndex;
use App\Http\Livewire\Ods\OdsForm;
use App\Http\Livewire\Pop\PopIndex;
use App\Http\Livewire\Pop\PopForm;
use App\Http\Livewire\InternetCustomerGroup\InternetCustomerGroupIndex;
use App\Http\Livewire\CoverageService\CoverageServiceIndex;
use App\Http\Livewire\CoverageService\CoverageServiceForm;
use App\Http\Livewire\InternetPackage\InternetPackageIndex;
use App\Http\Livewire\InternetPackage\InternetPackageForm;
use App\Http\Livewire\InternetCustomer\InternetCustomerForm;
use App\Http\Livewire\InternetCustomer\Admin\InternetCustomerIndex;
use App\Http\Livewire\InternetCustomer\Admin\InternetCustomerShow;
use App\Http\Livewire\InternetCustomer\InternetCustomerShow as CustomerShow;
use App\Http\Livewire\InternetCustomer\CustomerCodeInput;
use App\Http\Livewire\InternetCustomer\InternetCustomerUserRegionIndex;
use App\Http\Livewire\Promo\PromoIndex;
use App\Http\Livewire\Promo\PromoForm;
use App\Http\Livewire\Router\RouterForm;
use App\Http\Livewire\Router\RouterIndex;
use App\Http\Livewire\Router\RouterInventory;
use App\Http\Livewire\Router\PackageProfileMapping;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Webhook (public, no auth)
Route::post('wablas/webhook', [WablasWebhookController::class, 'handle']);
Route::post('xendit/webhook', [XenditController::class, 'handle']);
Route::post('keloola-pay/webhook', [XenditController::class, 'handleKeloolaPay']);
Route::post('midtrans/webhook', [MidtransController::class, 'handleNotification']);

// Internet Customer public routes
Route::get('internet-customer/customer-active', CustomerCodeInput::class)->name('internet-customer.customer');
Route::get('internet-customer/invoice/{purchaseId}', [InternetCustomerController::class, 'downloadInvoice'])->name('internet-customer.download-invoice');
Route::get('internet-customer/registration/{companyId}', InternetCustomerForm::class)->name('internet-customer.create');
Route::get('internet-customer/customer/{code}', CustomerShow::class)->name('internet-customer.customer.show');

// Internet Customer public registration API
Route::get('/api/cities/{provinceId}', [InternetCustomerController::class, 'getCities'])->name('api.cities');
Route::get('/api/districts/{cityId}', [InternetCustomerController::class, 'getDistricts'])->name('api.districts');
Route::get('/api/subdistricts/{districtId}', [InternetCustomerController::class, 'getSubdistricts'])->name('api.subdistricts');
Route::get('/api/coverage/{subdistrictId}', [InternetCustomerController::class, 'checkCoverage'])->name('api.coverage');

Route::post('internet-customer/store', [InternetCustomerController::class, 'store'])->name('internet-customers.store');
Route::post('internet-customer/check-promo', [InternetCustomerController::class, 'checkPromoAjax'])->name('internet-customers.check-promo');
Route::get('internet-customer/create', [InternetCustomerController::class, 'create'])->name('internet-customer.create_direct');

Route::get('internet-customer', InternetCustomerIndex::class)->name('internet-customer.index');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Auth::routes([
    'register' => false,
    'verify'   => true,
]);

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/home/internet-report', [App\Http\Controllers\HomeController::class, 'internetReport'])->name('home.internet-report')->middleware(['auth', 'ip.restriction']);

Route::group(['middleware' => ['auth','role.permission','ip.restriction']], function() {

    // ========================================================================
    // USER / ROLE / COMPANY
    // ========================================================================
    Route::get('user/profileEdit/{slug}', [UserController::class, 'profileEdit'])->name('user.profileEdit');
    Route::put('user/profileUpdate/{slug}', [UserController::class, 'profileUpdate'])->name('user.profileUpdate');
    Route::post('user/updatefcm', [UserController::class, 'updatefcm'])->name('user.updatefcm');
    Route::resource('user', UserController::class);

    Route::resource('role', RoleController::class);
    Route::post('role/updateName/{role}', [RoleController::class, 'updateName'])->name('role.update-name');
    Route::post('role/updateMenuPermissions/{role}', [RoleController::class, 'updateMenuPermissions'])->name('role.update-menu-permissions');
    Route::post('role/selectAll/{role}', [RoleController::class, 'selectAll'])->name('role.select-all');
    Route::post('role/deselectAll/{role}', [RoleController::class, 'deselectAll'])->name('role.deselect-all');
    Route::post('role/{role}/duplicate', [RoleController::class, 'duplicate'])->name('role.duplicate');
    Route::post('role/clearAllCache', [RoleController::class, 'clearAllCache'])->name('role.clear-all-cache');

    Route::resource('company', CompanyController::class)->except(['create', 'show']);
    Route::post('company/{company}/custom-slug', [CompanyController::class, 'storeCustomSlug'])->name('company.custom-slug.store');
    Route::delete('company/{company}/custom-slug/{customSlug}', [CompanyController::class, 'destroyCustomSlug'])->name('company.custom-slug.destroy');

    Route::resource('setting-company', SettingCompanyController::class)->only('index', 'store');

    // ========================================================================
    // REGION / WILAYAH (for internet customer addresses)
    // ========================================================================
    Route::get('city/select2', [CityController::class, 'select2'])->name('city.select2');
    Route::get('district/select2', [DistrictController::class, 'select2'])->name('district.select2');
    Route::get('wilayah/select2', [WilayahController::class, 'select2'])->name('wilayah.select2');

    Route::get('province/dataTableJson', [ProvinceController::class, 'dataTableJson'])->name('province.dataTableJson');
    Route::get('city/dataTableJson', [CityController::class, 'dataTableJson'])->name('city.dataTableJson');
    Route::get('district/dataTableJson', [DistrictController::class, 'dataTableJson'])->name('district.dataTableJson');
    Route::get('subdistrict/dataTableJson', [SubdistrictController::class, 'dataTableJson'])->name('subdistrict.dataTableJson');
    Route::get('postal-code/dataTableJson', [PostalCodeController::class, 'dataTableJson'])->name('postal-code.dataTableJson');

    Route::resources([
        'province'    => ProvinceController::class,
        'city'        => CityController::class,
        'district'    => DistrictController::class,
        'subdistrict' => SubdistrictController::class,
        'postal-code' => PostalCodeController::class,
    ]);

    // ========================================================================
    // INTERNET INFRASTRUCTURE (Router, ODS, POP, Coverage)
    // ========================================================================
    Route::get('router', RouterIndex::class)->name('router.index');
    Route::get('router/create', RouterForm::class)->name('router.create');
    Route::get('router/mass-move', \App\Http\Livewire\Router\RouterMassMove::class)->name('router.mass-move');
    Route::get('router/edit/{mikrotik}', RouterForm::class)->name('router.edit');
    Route::get('router/show/{routerId}', RouterInventory::class)->name('router.show');
    Route::get('router/mapping/{routerId}', PackageProfileMapping::class)->name('router.mapping');

    Route::get('data-center', Index::class)->name('data-center.index');
    Route::get('data-center/create', Form::class)->name('data-center.create');
    Route::get('data-center/edit/{id}', Form::class)->name('data-center.edit');

    Route::get('pop', PopIndex::class)->name('pop.index');
    Route::get('pop/create', PopForm::class)->name('pop.create');
    Route::get('pop/edit/{id}', PopForm::class)->name('pop.edit');

    Route::get('optical-distribution', OdsIndex::class)->name('optical-distribution.index');
    Route::get('optical-distribution/create', OdsForm::class)->name('optical-distribution.create');
    Route::get('optical-distribution/edit/{id}', OdsForm::class)->name('optical-distribution.edit');

    Route::get('coverage-service', CoverageServiceIndex::class)->name('coverage-service.index');
    Route::get('coverage-service/create', CoverageServiceForm::class)->name('coverage-service.create');
    Route::get('coverage-service/edit/{id}', CoverageServiceForm::class)->name('coverage-service.edit');

    // ========================================================================
    // INTERNET PACKAGES & PROMOS
    // ========================================================================
    Route::get('internet-package', InternetPackageIndex::class)->name('internet-package.index');
    Route::get('internet-package/create', InternetPackageForm::class)->name('internet-package.create');
    Route::get('internet-package/edit/{id}', InternetPackageForm::class)->name('internet-package.edit');

    Route::get('promo', PromoIndex::class)->name('promo.index');
    Route::get('promo/create', PromoForm::class)->name('promo.create');
    Route::get('promo/edit/{id}', PromoForm::class)->name('promo.edit');

    // ========================================================================
    // INTERNET CUSTOMERS
    // ========================================================================
    Route::get('internet-customer-group', InternetCustomerGroupIndex::class)->name('internet-customer-group.index');
    Route::get('internet-customer-user-region', InternetCustomerUserRegionIndex::class)->name('internet-customer-user-region.index');

    Route::put('internet-customer/update/{id}', InternetCustomerIndex::class)->name('internet-customer.update');
    Route::get('internet-customer/edit/{id}', InternetCustomerForm::class)->name('internet-customer.edit');
    Route::get('internet-customer/export/{format}', [InternetCustomerController::class, 'export'])->name('internet-customer.export');
    Route::get('internet-customer/{customerId}', InternetCustomerShow::class)->name('internet-customer.show');

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // ========================================================================
    // INTERNET ASSET
    // ========================================================================
    Route::get('internet-asset', \App\Http\Livewire\Asset\AssetIndex::class)->name('internet-asset.index');
    Route::get('internet-asset/create', \App\Http\Livewire\Asset\AssetForm::class)->name('internet-asset.create');
    Route::get('internet-asset/edit/{id}', \App\Http\Livewire\Asset\AssetForm::class)->name('internet-asset.edit');

    // ========================================================================
    // INTERNET REPORT
    // ========================================================================
    Route::get('internet-report', [\App\Http\Controllers\InternetReportController::class, 'index'])->name('internet-report.index');
    Route::get('internet-report/groupings', [\App\Http\Controllers\InternetReportController::class, 'groupings'])->name('internet-report.groupings');
    Route::get('internet-report/data', [\App\Http\Controllers\InternetReportController::class, 'data'])->name('internet-report.data');
});

// Error page
Route::get('error/{code?}', function ($code = 500) {
    return view('public_error', [
        'code'    => $code,
        'title'   => session('title', 'Terjadi Kesalahan'),
        'message' => session('message', 'Kesalahan tidak diketahui'),
        'icon'    => session('icon', 'fas fa-exclamation-triangle'),
    ]);
})->name('public.error');

Route::get('page/privacy-policy', function () {
    return view('policy');
})->name('page.privacy-policy');

Route::get('/robots.txt', function () {
    return response("User-agent: *\nDisallow: /storage/", 200)
        ->header('Content-Type', 'text/plain');
});
