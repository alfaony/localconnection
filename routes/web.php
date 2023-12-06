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
use App\Http\Controllers\RoleController;

use App\Http\Controllers\PricelistController;
use App\Http\Controllers\CompanyController;



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


Route::group(['middleware' => ['auth','role.permission']], function() 
{
  Route::resource('project', ProjectController::class);
  Route::resource('employee', EmployeeController::class);
  Route::resource('user', UserController::class);

  Route::delete('suplier/deletePurchase/purchase/{purchase}',[SuplierController::class,'deletePurchase'])->name('suplier.destroy.purchase');
  Route::get('suplier/productPrice/counting',[SuplierController::class,'productPrice'])->name('suplier.productPrice');
  Route::resource('suplier', SuplierController::class);

  Route::resource('report', ReportController::class)->only('index');

  Route::delete('manager/destroyJob/job/{job}',[ManagerController::class,'destroyJob'])->name('manager.destroy.job');
  Route::get('manager/counting',[ManagerController::class,'counting'])->name('manager.counting');
  Route::resource('manager', ManagerController::class);

  Route::resource('customer', CustomerController::class)->except(['create','show']);
  
  Route::resource('product', ProductController::class)->except(['create','show']);
  
  Route::delete('quote/destroyProduct/product/{QuoteProduct}',[QuoteController::class,'destroyProduct'])->name('quote.destroy.product');
  Route::get('quote/productPrice/counting',[QuoteController::class,'productPrice'])->name('quote.productPrice');
  Route::get('quote/select2', [QuoteController::class, 'select2'])->name('quote.select2');
  Route::get('quote/dataTableJson', [QuoteController::class, 'dataTableJson'])->name('quote.datatable');
  Route::get('quote/downloadPdf/pdf/{slug}',[QuoteController::class,'downloadPdf'])->name('quote.download.pdf');
  Route::get('quote/counting',[QuoteController::class,'counting'])->name('quote.counting');
  Route::get('quote/productCounting/counting',[QuoteController::class,'productCounting'])->name('quote.productCounting');
  Route::resource('quote', QuoteController::class)->except(['show']);

  Route::delete('work-order/destroyProduct/product/{WorkOrderProduct}',[WorkOrderController::class,'destroyProduct'])->name('work-order.destroy.product');
  Route::get('work-order/productPrice/counting', [WorkOrderController::class, 'productPrice'])->name('work-order.productPrice');
  Route::get('work-order/select2', [WorkOrderController::class, 'select2'])->name('work-order.select2');
  Route::get('work-order/downloadPdf/pdf/{slug}/',[WorkOrderController::class,'downloadPdf'])->name('work-order.download.pdf');
  Route::get('work-order/suggestionQuote/{id}/',[WorkOrderController::class,'suggestionQuote'])->name('work-order.suggestionQuote');
  Route::get('work-order/dataTableJson', [WorkOrderController::class, 'dataTableJson'])->name('work-order.datatable');
  Route::get('work-order/productCounting/counting',[WorkOrderController::class,'productCounting'])->name('work-order.productCounting');
  Route::resource('work-order', WorkOrderController::class)->except(['show']);
  
  Route::get('agreement-letter/downloadPdf/pdf/{slug}/',[AgreementLetterController::class,'downloadPdf'])->name('agreement-letter.download.pdf');
  Route::get('agreement-letter/dataTableJson', [AgreementLetterController::class, 'dataTableJson'])->name('agreement-letter.datatable');
  Route::resource('agreement-letter', AgreementLetterController::class)->except(['show']);

  Route::get('bast/downloadPdf/pdf/{slug}',[BastController::class,'downloadPdf'])->name('bast.download.pdf');
  Route::get('bast/dataTableJson', [BastController::class, 'dataTableJson'])->name('bast.datatable');
  Route::resource('bast', BastController::class)->except(['show']);

  Route::delete('report-project/destroyDetail/{ReportProjectDetail}',[ReportProjectController::class,'destroyDetail'])->name('report-project.destroy.detail');
  Route::get('report-project/datatable', [ReportProjectController::class, 'dataTableJson'])->name('report-project.datatable');
  Route::resource('report-project', ReportProjectController::class)->except(['show']);

  Route::resource('setting-company', SettingCompanyController::class)->only('index','store');
  Route::resource('role', RoleController::class);


  Route::get('pricelist/dataTableJson', [PricelistController::class, 'dataTableJson'])->name('pricelist.datatable');
  Route::get('pricelist', [PricelistController::class, 'index'])->name('pricelist.index');
  Route::get('pricelist/show/{product}', [PricelistController::class, 'show'])->name('pricelist.show');
  
  Route::resource('company', CompanyController::class);
});



