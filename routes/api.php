<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\PricelistController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\QuoteController;
use App\Http\Controllers\API\WorkOrderController;
use App\Http\Controllers\API\AgreementLetterController;
use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\API\DailyTaskController;
use App\Http\Controllers\API\ObjectiveController;
use App\Http\Controllers\API\DailyTaskProjectController;
use App\Http\Controllers\API\UsedLaptopController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [LoginController::class, 'login']);

Route::group(['middleware' => ['auth:api','role.permission.api']], function() 
{
    Route::resource('customer', CustomerController::class)->except(['create','show']);
    
    Route::resource('product', ProductController::class)->except(['create','show']);
    
    Route::resource('pricelist', PricelistController::class)->only('index','show');
    
    Route::get('quote/downloadPdf/pdf/{slug}',[QuoteController::class,'downloadPdf']);
    Route::resource('quote', QuoteController::class);
    
    Route::get('work-order/downloadPdf/pdf/{slug}',[WorkOrderController::class,'downloadPdf']);
    Route::resource('work-order', WorkOrderController::class);

    Route::get('agreement-letter/downloadPdf/pdf/{slug}/',[AgreementLetterController::class,'downloadPdf'])->name('agreement-letter.download.pdf');;
    Route::resource('agreement-letter', AgreementLetterController::class);

});

Route::group(['middleware' => ['auth:api']], function() 
{
    Route::post('provision', [MikrotikController::class, 'provision']);   
    Route::post('cut', [MikrotikController::class, 'cut']);
    Route::post('restore', [MikrotikController::class, 'restore']);
});
    Route::resource('dailytask', DailyTaskController::class);
    Route::put('dailytask/statuschange/{slug}', [DailyTaskController::class,'statuschange'])->name('dailytask.statuschange');
    Route::put('dailytask/report/{slug}', [DailyTaskController::class, 'report']);

    Route::get('daily_task_project/getcustomfield/{project}', [DailyTaskProjectController::class,'getcustomfield'])->name('getcustomfield');

    Route::get('objective/getresult/{objective}', [ObjectiveController::class,'getresult'])->name('getresult');
    // Route::post('dailytask/{slug}/approve', [DailyTaskController::class, 'approvement']);
    // Route::post('dailytask/{slug}/extend', [DailyTaskController::class, 'extend']);
    Route::patch('used-laptop/maskAsSold/{id}', [UsedLaptopController::class,'maskAsSold'])->name('used-laptop.maskAsSold');
