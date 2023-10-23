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
  Route::get('quote/download/pdf/{slug}/{nomor}',[QuoteController::class,'downloadPdf'])->name('quote.download.pdf');
  Route::get('quote/counting',[QuoteController::class,'counting'])->name('quote.counting');
  Route::get('quote/product/counting',[QuoteController::class,'productCounting'])->name('quote.productCounting');
  Route::resource('quote', QuoteController::class)->except(['show']);
});

