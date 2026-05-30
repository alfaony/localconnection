<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\API\RegionController;
use App\Http\Controllers\API\InternetCustomerController;
use App\Http\Controllers\API\UserApiController;
use App\Http\Controllers\API\InternetCustomerApiController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('login', [LoginController::class, 'login']);
Route::post('login_flutter', [LoginController::class, 'login_flutter']);

// Region (public, no auth - for address selection in internet customer registration)
Route::prefix('region')->group(function () {
    Route::get('/getCountries', [RegionController::class, 'getCountries']);
    Route::get('/getProvinces', [RegionController::class, 'getProvinces']);
    Route::get('/getCities', [RegionController::class, 'getCities']);
    Route::get('/getDistricts', [RegionController::class, 'getDistricts']);
    Route::get('/getSubdistricts', [RegionController::class, 'getSubdistricts']);
});

Route::group(['middleware' => ['auth:api', 'role.permission.api']], function () {

    Route::get('users', [UserApiController::class, 'indexUsers']);

    // Internet Customer API
    Route::prefix('internet-customers')->controller(InternetCustomerApiController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::post('/{id}/approve', 'approve');
        Route::post('/{id}/close', 'close');
        Route::post('/{id}/complete-installation', 'completeInstallation');
        Route::get('/{id}/installation-resources', 'getInstallationResources');
        Route::get('/get-ip-pools/by-router', 'getIpPoolsByRouter');
    });

    Route::post('internet-customer/import', [InternetCustomerController::class, 'import']);
});

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('provision', [MikrotikController::class, 'provision']);
    Route::post('cut', [MikrotikController::class, 'cut']);
    Route::post('restore', [MikrotikController::class, 'restore']);

    Route::post('/user/fcm-token', [UserController::class, 'saveFcmToken']);
    Route::get('/user/role-division', [UserController::class, 'getRoleAndDivision']);
    Route::post('logout', [LoginController::class, 'logout']);

    Route::post('test', function (Request $request) {
        \Illuminate\Support\Facades\Log::info($request->all());
    });
});
