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
use App\Http\Controllers\SortUrlController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\EquipmentReductionController;

use App\Http\Controllers\TaskController;

use App\Http\Controllers\TaskAssignController;
use App\Http\Controllers\TasStatusController;
use App\Http\Controllers\TaskTypeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportPointController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetAssignController;
use App\Http\Controllers\SecurityCheckController;
use App\Http\Controllers\CctvCheckController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\IpRightController;
use App\Http\Controllers\SalesAchievementController;
use App\Http\Controllers\ReportPointProductivityController;
use App\Http\Controllers\DailyTaskController;
use App\Http\Controllers\DailyTaskProjectController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\ObjectiveController;
use App\Http\Controllers\VisionController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\ProjectDashboardController;
use App\Http\Controllers\DailyTaskCategoryController;
use App\Http\Controllers\DivisionBudgetController;


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
  
  Route::get('user/profileEdit/{slug}', [UserController::class,'profileEdit'])->name('user.profileEdit');
  Route::put('user/profileUpdate/{slug}', [UserController::class,'profileUpdate'])->name('user.profileUpdate');
  Route::resource('user', UserController::class);

  Route::delete('suplier/deletePurchase/purchase/{purchase}',[SuplierController::class,'deletePurchase'])->name('suplier.destroy.purchase');
  Route::get('suplier/suggestionWorkOrder/{id}/',[SuplierController::class,'suggestionWorkOrder'])->name('suplier.suggestionWorkOrder');
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

  Route::resource('company', CompanyController::class)->except(['create','show']);

  Route::get('equipment/history/{slug}', [EquipmentController::class, 'history'])->name('equipment.history');
  Route::resource('equipment', EquipmentController::class);

  Route::resource('equipment-reduction', EquipmentReductionController::class);

  Route::resource('task', TaskController::class)->except(['create','show']);

  Route::put('task-assign/approvement/{slug}', [TaskAssignController::class, 'approvement'])->name('task-assign.approvement');
  Route::put('task-assign/report/{slug}', [TaskAssignController::class, 'report'])->name('task-assign.report');
  Route::resource('task-assign', TaskAssignController::class);

  Route::get('report-point',[ReportPointController::class,'index'])->name('report-point.index');

  Route::resource('attendance', AttendanceController::class)->only('index');

  Route::resource('asset', AssetController::class)->except(['create']);
  Route::resource('asset-assign', AssetAssignController::class)->only(['store','update','destroy']);

  Route::resource('security-check', SecurityCheckController::class);

  Route::resource('cctv-check', CctvCheckController::class);

  Route::resource('ticket', TicketController::class)->except(['create','store']);
  
  Route::resource('training', TrainingController::class);
  Route::put('training/addpoint/{slug}', [TrainingController::class, 'addpoint'])->name('training.addPoint');
  
  Route::resource('ip-right', IpRightController::class);
  Route::put('ip-right/addpoint/{slug}', [IpRightController::class, 'addpoint'])->name('ip-right.addPoint');
  
  Route::resource('sales_achievement', SalesAchievementController::class);
  Route::put('sales_achievement/addpoint/{slug}', [SalesAchievementController::class, 'addpoint'])->name('sales_achievement.addPoint');
  
  Route::get('report-productivity',[ReportPointProductivityController::class,'index'])->name('report-productivity.index');

  Route::put('dailytask/storesubtask/{slug}', [DailyTaskController::class,'storesubtask'])->name('dailytask.storesubtask');
  Route::put('dailytask/comment/{slug}', [DailyTaskController::class,'comment'])->name('dailytask.comment');
  Route::put('dailytask/extend/{slug}', [DailyTaskController::class,'extend'])->name('dailytask.extend');
  Route::put('dailytask/updatemedia/{slug}', [DailyTaskController::class,'updatemedia'])->name('dailytask.updatemedia');
  Route::put('dailytask/report/{slug}', [DailyTaskController::class,'report'])->name('dailytask.report');
  Route::delete('dailytask/deletemedia/{id}', [DailyTaskController::class, 'deletemedia'])->name('dailytask.deletemedia');
  Route::put('dailytask/approvement/{slug}', [DailyTaskController::class,'approvement'])->name('dailytask.approvement');
  Route::resource('dailytask', DailyTaskController::class);
  
  Route::put('daily_task_project/customfieldstore/{slug}', [DailyTaskProjectController::class, 'customfieldstore'])->name('customfieldstore');
  Route::put('daily_task_project/customfieldupdate/{id}', [DailyTaskProjectController::class, 'customfieldupdate'])->name('customfieldupdate');
  Route::delete('daily_task_project/customfielddestroy/{id}', [DailyTaskProjectController::class, 'customfielddestroy'])->name('customfielddestroy');
  Route::get('daily_task_project/getcustomfield/{project}', [DailyTaskProjectController::class,'getcustomfield'])->name('getcustomfield');
  Route::get('daily_task_project/showproject/{slug}', [DailyTaskProjectController::class,'showproject'])->name('daily_task_project.showproject');
  Route::resource('daily_task_project', DailyTaskProjectController::class);


  
  Route::get('objective/showtask/{objective}', [ObjectiveController::class,'showtask'])->name('objective.showtask');
  Route::get('objective/getresult/{objective}', [ObjectiveController::class,'getresult'])->name('getresult');
  Route::resource('objective', ObjectiveController::class);
  Route::resource('division', DivisionController::class);
  Route::put('dailytask/statuschange/{slug}', [DailyTaskController::class,'statuschange'])->name('dailytask.statuschange');
  
  
  Route::resource('vision', VisionController::class);
  Route::resource('mission', MissionController::class)->except(['index', 'show', 'create', 'edit']);

  Route::get('project-dashboard/fetchusertask/{userId}/{filter}', [ProjectDashboardController::class, 'fetchusertask'])->name('fetchusertask');
  Route::get('project-dashboard', [ProjectDashboardController::class,'index'])->name('projectdashboard.index');

  Route::resource('daily-task-category', DailyTaskCategoryController::class);

  Route::resource('division-budget', DivisionBudgetController::class);
  Route::post('division-budget/approve/{divisionBudget}', [DivisionBudgetController::class, 'approve'])->name('division-budget.approve');
});

// Add this to your routes/web.php




Route::post('bos-ticket', [TicketController::class,'store'])->name('bos-ticket.store');
Route::get('bos-ticket', [TicketController::class,'create'])->name('bos-ticket.create');;



Route::get('/{slug}',[SortUrlController::class,'index'])->name('download.index');



