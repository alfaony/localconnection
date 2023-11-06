<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SuplierController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\AgreementLetterController;
use App\Http\Controllers\BastController;
use App\Http\Controllers\ReportProjectController;
use App\Http\Controllers\SettingCompanyController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->to('/home');
});

Auth::routes([
    'register' => false, // Registration Routes...
  ]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth']], function() 
{
  Route::resource('project', ProjectController::class);
  Route::resource('employee', EmployeeController::class);
  Route::resource('user', UserController::class);

  Route::delete('suplier/delete/purchase/{purchase}',[SuplierController::class,'deletePurchase'])->name('suplier.destroy.purchase');
  Route::resource('suplier', SuplierController::class);

  Route::resource('report', ReportController::class)->only('index');

  Route::delete('manager/delete/job/{job}',[ManagerController::class,'destroyJob'])->name('manager.destroy.job');
  Route::get('manager/counting',[ManagerController::class,'counting'])->name('manager.counting');
  Route::resource('manager', ManagerController::class);

  Route::resource('customer', CustomerController::class)->except(['create','show']);
  
  Route::resource('product', ProductController::class)->except(['create','show']);
  
  Route::delete('quote/delete/product/{QuoteProduct}',[QuoteController::class,'destroyProduct'])->name('quote.destroy.product');
  Route::get('quote/select2', [QuoteController::class, 'select2'])->name('quote.select2');
  Route::get('quote/datatable', [QuoteController::class, 'dataTableJson'])->name('quote.datatable');
  Route::get('quote/download/pdf/{slug}',[QuoteController::class,'downloadPdf'])->name('quote.download.pdf');
  Route::get('quote/counting',[QuoteController::class,'counting'])->name('quote.counting');
  Route::get('quote/product/counting',[QuoteController::class,'productCounting'])->name('quote.productCounting');
  Route::get('quote/datatable', [QuoteController::class, 'dataTableJson'])->name('quote.datatable');
  Route::resource('quote', QuoteController::class)->except(['show']);

  Route::delete('work-order/delete/product/{WorkOrderProduct}',[WorkOrderController::class,'destroyProduct'])->name('work-order.destroy.product');
  Route::get('work-order/select2', [WorkOrderController::class, 'select2'])->name('work-order.select2');
  Route::get('work-order/download/pdf/{slug}/',[WorkOrderController::class,'downloadPdf'])->name('work-order.download.pdf');
  Route::get('work-order/suggestionQuote/{id}/',[WorkOrderController::class,'suggestionQuote'])->name('work-order.suggestionQuote');
  Route::get('work-order/datatable', [WorkOrderController::class, 'dataTableJson'])->name('work-order.datatable');
  Route::get('work-order/product/counting',[WorkOrderController::class,'productCounting'])->name('work-order.productCounting');
  Route::resource('work-order', WorkOrderController::class)->except(['show']);
  
  Route::get('agreement-letter/download/pdf/{slug}/',[AgreementLetterController::class,'downloadPdf'])->name('agreement-letter.download.pdf');
  Route::get('agreement-letter/datatable', [AgreementLetterController::class, 'dataTableJson'])->name('agreement-letter.datatable');
  Route::resource('agreement-letter', AgreementLetterController::class)->except(['show']);

  Route::get('bast/download/pdf/{slug}',[BastController::class,'downloadPdf'])->name('bast.download.pdf');
  Route::get('bast/datatable', [BastController::class, 'dataTableJson'])->name('bast.datatable');
  Route::resource('bast', BastController::class)->except(['show']);

  Route::get('report-project/datatable', [ReportProjectController::class, 'dataTableJson'])->name('report-project.datatable');
  Route::resource('report-project', ReportProjectController::class)->except(['show']);

  Route::resource('setting-company', SettingCompanyController::class)->only('index','store');
});

