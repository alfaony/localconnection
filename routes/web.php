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
use App\Http\Controllers\ShiftingObController;
use App\Http\Controllers\ScheduleObController;
use App\Http\Controllers\DivisionBudgetController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\LetterSubmissionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\NationalHolidayController;
use App\Http\Controllers\EmployeeCheckingController;
use App\Http\Controllers\XeroController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\XeroWebhookController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PassCheckingController;
use App\Http\Controllers\KyeController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\RackController;
use App\Http\Controllers\ShippingRateController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\SubdistrictController;
use App\Http\Controllers\PostalCodeController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ShippingCalculationController;
use App\Http\Controllers\AskBosController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\PartnershipAgreementController;
use App\Http\Controllers\SupplierCategoryController;
use App\Http\Controllers\ProductSupplierController;
use App\Http\Controllers\DayoffController;
use App\Http\Controllers\OfficeMediaController;
use App\Http\Controllers\WeeklyReportController;
use App\Http\Controllers\ReportChartController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\SubscribeLetterController;
use App\Http\Controllers\FlowChartController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\ItemPurchaseController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\WablasWebhookController;
use App\Http\Controllers\BroadcastAuthController;
use App\Http\Controllers\PotentialVendorController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\GoogleMeetController;
use App\Http\Controllers\MomController;
use App\Http\Controllers\UsedLaptopController;
use App\Http\Controllers\MasterCheckItemController;
use App\Http\Controllers\UsedItemController;
use App\Http\Controllers\BarcodeAttendanceController;
use App\Http\Controllers\OfficeAttendanceController;
use App\Http\Controllers\SaleController;

// LiveWired
use App\Http\Livewire\DataCenter\Index;
use App\Http\Livewire\DataCenter\Form;
use App\Http\Livewire\Pop\PopIndex;
use App\Http\Livewire\Pop\PopForm;
use App\Http\Livewire\Ods\OdsIndex;
use App\Http\Livewire\Ods\OdsForm;
use App\Http\Livewire\CoverageService\CoverageServiceIndex;
use App\Http\Livewire\CoverageService\CoverageServiceForm;
use App\Http\Livewire\InternetPackage\InternetPackageIndex;
use App\Http\Livewire\InternetPackage\InternetPackageForm;
use App\Http\Livewire\InternetCustomer\InternetCustomerForm;
use App\Http\Livewire\InternetCustomer\Admin\InternetCustomerIndex;
use App\Http\Livewire\InternetCustomer\Admin\InternetCustomerShow;
use App\Http\Livewire\InternetCustomer\InternetCustomerShow as CustomerShow;
use App\Http\Livewire\Promo\PromoIndex;
use App\Http\Livewire\Promo\PromoForm;
use App\Http\Livewire\WebhookSettingTable;
use App\Http\Livewire\ProductSupplierTypeIndex;
use App\Http\Livewire\ProductStore\ProductStoreIndex;
use App\Http\Livewire\ProductStore\ProductStoreShow;
use App\Http\Livewire\ProductStore\ProductStoreForm;
use App\Http\Livewire\ProductStore\ProductStorePrint;
use App\Http\Livewire\Sale\SaleIndex;
use App\Http\Livewire\Sale\SaleShow;
use App\Http\Livewire\BrandProductStoreIndex;
use App\Http\Livewire\CategoryProductStoreIndex;


use App\Http\Livewire\PunishmentUserTable;



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
Route::post('wablas/webhook', [WablasWebhookController::class, 'handle']);
Route::post('xero/webhook', [XeroWebhookController::class, 'handleWebhook'])->middleware('verify.xero.signature');

Route::get('xero/check/{id}', [XeroWebhookController::class, 'isCheckingInvoice']);

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('validate.vendor.token')->group(function () 
{
    Route::get('item-request/respond/{id}/{token}', [PotentialVendorController::class, 'edit'])->name('vendor.respond');
    Route::post('item-request/respond/{id}/{token}', [PotentialVendorController::class, 'update'])->name('vendor.respond.submit');
});
// Public route (tidak membutuhkan login)
Route::get('item-request/list/{companySlug}', [ItemRequestController::class, 'publicIndex'])->name('item-request.public.index');
Route::get('item-request/ajax/{companySlug}', [ItemRequestController::class, 'loadByCompany']);

Route::group(['middleware' => ['auth','web']], function(){
  Route::post('broadcasting/authorize', [BroadcastAuthController::class, 'broadcastingAuthorize'])->name('broadcasting.authorize');
});

Route::group(['prefix' => 'google'], function () {
  Route::get('oauth', [GoogleMeetController::class, 'redirectToGoogle'])->name('google.auth')->middleware('auth');
  Route::get('oauth/callback', [GoogleMeetController::class, 'handleGoogleCallback']);
});

Route::group(['prefix' => 'mom/external'], function () 
{
  Route::get('task/{token}', [MomController::class, 'viewExternalTask'])->name('external.task.view');
  Route::post('task/{token}/submit', [MomController::class, 'submitExternalTask'])->name('external.task.submit');
});

// Route::group(['prefix' => 'meeting/public'], function() {
//     Route::get('oauth/callback', [MeetingController::class, 'handleGoogleCallbackPublic'])->name('meeting.public.callback');
//     Route::view('error', 'meeting.public.error')->name('meeting.public.error');
//     Route::get('join/{slug}/{token}', [MeetingController::class, 'redirectToGooglePublic'])->name('meeting.public.join');
// });
Route::prefix('meeting/public')->group(function () {
    Route::view('error', 'meeting.public_error')->name('meeting.public.error');
    Route::get('join/{slug}/{token}', [MeetingController::class, 'showPublicJoinForm'])->name('meeting.public.join');
    Route::post('join/{slug}/{token}', [MeetingController::class, 'submitPublicJoinForm'])->name('meeting.public.join.submit');
});

Route::group(['middleware' => ['auth','web', 'ensure.xero.connected','role.permission']], function(){
  Route::get('xero',function(){
    
    return redirect('/invoice')->with('xero',true);
    
  });
  
  Route::get('invoice/downloadPdfA/{slug}',[InvoiceController::class,'downloadPdfA'])->name('invoice.download.pdfa');
  Route::get('invoice/checkPdfAStatus', [InvoiceController::class, 'checkPdfAStatus'])->name('invoice.checkPdfAStatus');
  Route::post('invoice/clearsessionPdfA', [InvoiceController::class, 'clearsessionPdfA'])->name('invoice.clearsessionPdfA');
  Route::delete('invoice/destroyProduct/product/{invoiceProduct}',[invoiceController::class,'destroyProduct'])->name('invoice.destroy.product');
  Route::get('invoice/history/{slug}', [InvoiceController::class, 'history'])->name('invoices.history');
  Route::get('invoice/export/{format}', [InvoiceController::class, 'export'])->name('invoice.export');
  Route::get('invoice/checkExportStatus', [InvoiceController::class, 'checkExportStatus'])->name('invoice.checkExportStatus');
  Route::get('invoice/clearsession', [InvoiceController::class, 'clearsession'])->name('invoice.clearsession');
  Route::get('invoice/productPrice/counting',[invoiceController::class,'productPrice'])->name('invoice.productPrice');
  Route::get('invoice/select2', [invoiceController::class, 'select2'])->name('invoice.select2');
  Route::get('invoice/dataTableJson', [invoiceController::class, 'dataTableJson'])->name('invoice.datatable');
  Route::get('invoice/downloadPdf/pdf/{slug}',[invoiceController::class,'downloadPdf'])->name('invoice.download.pdf');
  Route::get('invoice/counting',[invoiceController::class,'counting'])->name('invoice.counting');
  Route::get('invoice/productCounting/counting',[invoiceController::class,'productCounting'])->name('invoice.productCounting');
  Route::get('invoice/suggestionQuote/{id}/',[invoiceController::class,'suggestionQuote'])->name('invoice.suggestionQuote');
  
  Route::post('invoice/sentMail/{slug}', [InvoiceController::class, 'sentMail'])->name('invoice.sentMail');
  Route::resource('invoice', invoiceController::class);
});


Auth::routes([
  'register' => false, // Registration Routes...
]);

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('employee-checking/report', [EmployeeCheckingController::class, 'report'])->name('employee-checking.report');

Route::get('partnership-agreement/sharePdf/{id}',[PartnershipAgreementController::class,'sharePdf'])->name('partnership-agreement.sharePdf');
Route::put('partnership-agreement/signatureShare/{id}',[PartnershipAgreementController::class,'signatureShare'])->name('partnership-agreement.signatureShare');

Route::get('used-laptop/showQr/{slug}', [UsedLaptopController::class,'showQr'])->name('used-laptop.show-qr');
Route::get('used-item/showQr/{slug}', [UsedItemController::class,'showQr'])->name('used-item.show-qr');

Route::group(['middleware' => ['auth','role.permission','ip.restriction']], function()
{
  Route::get('home/meetingAgenda', [App\Http\Controllers\HomeController::class, 'meetingAgenda'])->name('home.meetingAgenda');
  Route::get('home/listDayoff', [App\Http\Controllers\HomeController::class, 'listDayoff'])->name('home.listDayoff');
  Route::get('home/dashboardReport', [App\Http\Controllers\HomeController::class, 'dashboardReport'])->name('home.dashboardReport');
  Route::get('home/leaderboard', [App\Http\Controllers\HomeController::class, 'leaderboard'])->name('home.leaderboard');
  Route::get('home/overdueRanking', [App\Http\Controllers\HomeController::class, 'overdueRanking'])->name('home.overdueRanking');

  Route::resource('office-media', OfficeMediaController::class)->only(['index', 'store','destroy']);
  // Xero Setting
  Route::get('xero/connect', [XeroController::class,'connect']);
  Route::get('xero/disconnect', [XeroController::class,'disconnect']);
  
  Route::get('project/export', [ProjectController::class,'export'])->name('project.export');
  Route::resource('project', ProjectController::class);
  Route::resource('employee', EmployeeController::class);
  
  Route::get('user/profileEdit/{slug}', [UserController::class,'profileEdit'])->name('user.profileEdit');
  Route::put('user/profileUpdate/{slug}', [UserController::class,'profileUpdate'])->name('user.profileUpdate');
  Route::post('user/updatefcm',[UserController::class,'updatefcm'])->name('user.updatefcm');
  Route::resource('user', UserController::class);

  Route::delete('suplier/deletePurchase/purchase/{purchase}',[SuplierController::class,'deletePurchase'])->name('suplier.destroy.purchase');
  Route::get('suplier/suggestionWorkOrder/{id}/',[SuplierController::class,'suggestionWorkOrder'])->name('suplier.suggestionWorkOrder');
  Route::get('suplier/productPrice/counting',[SuplierController::class,'productPrice'])->name('suplier.productPrice');
  Route::resource('suplier', SuplierController::class);

  Route::resource('report', ReportController::class)->only('index');

  Route::delete('manager/destroyJob/job/{job}',[ManagerController::class,'destroyJob'])->name('manager.destroy.job');
  Route::get('manager/counting',[ManagerController::class,'counting'])->name('manager.counting');
  Route::resource('manager', ManagerController::class);

  Route::resource('customer', CustomerController::class)->except(['create']);

  Route::resource('product', ProductController::class)->except(['create','show']);
  Route::resource('product-category', ProductCategoryController::class);

  Route::delete('quote/destroyProduct/product/{QuoteProduct}',[QuoteController::class,'destroyProduct'])->name('quote.destroy.product');
  Route::get('quote/export/{format}', [QuoteController::class, 'export'])->name('quote.export');
  Route::get('quote/checkExportStatus', [QuoteController::class, 'checkExportStatus'])->name('quote.checkExportStatus');
  Route::get('quote/clearsession', [QuoteController::class, 'clearsession'])->name('quote.clearsession');
  Route::get('quote/productPrice/counting',[QuoteController::class,'productPrice'])->name('quote.productPrice');
  Route::get('quote/select2', [QuoteController::class, 'select2'])->name('quote.select2');
  Route::get('quote/dataTableJson', [QuoteController::class, 'dataTableJson'])->name('quote.datatable');
  Route::get('quote/downloadPdf/pdf/{slug}',[QuoteController::class,'downloadPdf'])->name('quote.download.pdf');
  Route::get('quote/counting',[QuoteController::class,'counting'])->name('quote.counting');
  Route::get('quote/productCounting/counting',[QuoteController::class,'productCounting'])->name('quote.productCounting');
  Route::resource('quote', QuoteController::class)->except(['show']);

  Route::delete('work-order/destroyProduct/product/{WorkOrderProduct}',[WorkOrderController::class,'destroyProduct'])->name('work-order.destroy.product');
  Route::get('work-order/export/{format}', [WorkOrderController::class, 'export'])->name('work-order.export');
  Route::get('work-order/checkExportStatus', [WorkOrderController::class, 'checkExportStatus'])->name('work-order.checkExportStatus');
  Route::get('work-order/clearsession', [WorkOrderController::class, 'clearsession'])->name('work-order.clearsession');
  Route::get('work-order/dataTableJsonQuoteWithoutWorkOrder', [WorkOrderController::class, 'dataTableJsonQuoteWithoutWorkOrder'])->name('work-order.dataTableJsonQuoteWithoutWorkOrder');
  Route::get('work-order/createsuggest/{slug}', [WorkOrderController::class, 'createsuggest'])->name('work-order.createsuggest');
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
  
  Route::post('bast/sendBastEmail/{slug}', [BastController::class, 'sendBastEmail'])->name('bast.sendEmail');
  Route::get('bast/export/{format}', [BastController::class, 'export'])->name('bast.export');
  Route::get('bast/checkExportStatus', [BastController::class, 'checkExportStatus'])->name('bast.checkExportStatus');
  Route::get('bast/clearsession', [BastController::class, 'clearsession'])->name('bast.clearsession');
  Route::get('bast/createsuggest/{slug}', [BastController::class, 'createsuggest'])->name('bast.createsuggest');
  Route::get('bast/dataTableJsonWorkOrderWithoutBast', [BastController::class, 'dataTableJsonWorkOrderWithoutBast'])->name('bast.dataTableJsonWorkOrderWithoutBast');
  Route::get('bast/downloadPdf/pdf/{slug}',[BastController::class,'downloadPdf'])->name('bast.download.pdf');
  Route::get('bast/dataTableJson', [BastController::class, 'dataTableJson'])->name('bast.datatable');
  Route::post('bast/requestReport', [BastController::class, 'requestReport'])->name('bast.requestReport');
  Route::resource('bast', BastController::class);

  Route::get('report-project/downloadall/{slug}', [ReportProjectController::class, 'downloadall'])->name('report-project.downloadall');
  Route::get('report-project/createsuggest/{slug}', [ReportProjectController::class, 'createsuggest'])->name('report-project.createsuggest');
  Route::get('report-project/dataTableJsonWorkOrderWithoutReportProject', [ReportProjectController::class, 'dataTableJsonWorkOrderWithoutReportProject'])->name('report-project.dataTableJsonWorkOrderWithoutReportProject');
  Route::get('report-project/datatable', [ReportProjectController::class, 'dataTableJson'])->name('report-project.datatable');
  Route::put('report-project/approvement/{slug}', [ReportProjectController::class, 'approvement'])->name('report-project.approvement');
  Route::delete('report-project/destroyDetail/{ReportProjectDetail}',[ReportProjectController::class,'destroyDetail'])->name('report-project.destroy.detail');
  Route::resource('report-project', ReportProjectController::class);

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

  Route::resource('attendance', AttendanceController::class);

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

  Route::get('/dailytask/export', [DailyTaskController::class, 'export'])->name('dailytask.export');
  Route::get('dailytask/template', [DailyTaskController::class, 'template'])->name('dailytask.template');
  Route::get('dailytask/downloadtemplate', [DailyTaskController::class, 'downloadtemplate'])->name('dailytask.downloadtemplate');
  Route::post('dailytask/checkDivisionQuota', [DailyTaskController::class, 'checkDivisionQuota'])->name('dailytask.checkDivisionQuota');
  Route::post('dailytask/import', [DailyTaskController::class, 'import'])->name('dailytask.import');
  Route::put('dailytask/storesubtask/{slug}', [DailyTaskController::class,'storesubtask'])->name('dailytask.storesubtask');
  Route::put('dailytask/comment/{slug}', [DailyTaskController::class,'comment'])->name('dailytask.comment');
  Route::put('dailytask/extend/{slug}', [DailyTaskController::class,'extend'])->name('dailytask.extend');
  Route::put('dailytask/updatemedia/{slug}', [DailyTaskController::class,'updatemedia'])->name('dailytask.updatemedia');
  Route::put('dailytask/report/{slug}', [DailyTaskController::class,'report'])->name('dailytask.report');
  Route::delete('dailytask/deletemedia/{id}', [DailyTaskController::class, 'deletemedia'])->name('dailytask.deletemedia');
  Route::put('dailytask/approvement/{slug}', [DailyTaskController::class,'approvement'])->name('dailytask.approvement');
  Route::put('dailytask/statuschange/{slug}', [DailyTaskController::class,'statuschange'])->name('dailytask.statuschange');
  Route::resource('dailytask', DailyTaskController::class);
  
  Route::get('daily_task_project/kanban/{slug}', [DailyTaskProjectController::class,'kanban'])->name('daily_task_project.kanban');
  Route::post('dailytask/updatestatus', [DailyTaskController::class, 'updatestatus'])->name('dailytask.updatestatus');
  Route::put('dailytask/assign/{slug}', [DailyTaskController::class, 'assign'])->name('dailytask.assign');
  Route::put('daily_task_project/customfieldstore/{slug}', [DailyTaskProjectController::class, 'customfieldstore'])->name('customfieldstore');
  Route::put('daily_task_project/customfieldupdate/{id}', [DailyTaskProjectController::class, 'customfieldupdate'])->name('customfieldupdate');
  Route::delete('daily_task_project/customfielddestroy/{id}', [DailyTaskProjectController::class, 'customfielddestroy'])->name('customfielddestroy');
  Route::get('daily_task_project/getcustomfield/{project}', [DailyTaskProjectController::class,'getcustomfield'])->name('getcustomfield');
  Route::get('daily_task_project/showproject/{slug}', [DailyTaskProjectController::class,'showproject'])->name('daily_task_project.showproject');
  Route::get('daily_task_project/createdailytask/{slug}', [DailyTaskProjectController::class,'createdailytask'])->name('daily_task_project.createdailytask');
  Route::resource('daily_task_project', DailyTaskProjectController::class);
  
  Route::get('objective/showtask/{objective}', [ObjectiveController::class,'showtask'])->name('objective.showtask');
  Route::get('objective/getresult/{objective}', [ObjectiveController::class,'getresult'])->name('getresult');
  Route::resource('objective', ObjectiveController::class);

  Route::get('division/ajaxDivisionTasks/{division}', [DivisionController::class, 'ajaxDivisionTasks'])->name('divisions.ajax.tasks');
  Route::get('division/fetchusertask/{userId}/{filter}', [DivisionController::class, 'fetchusertask'])->name('division.fetchusertask');
  Route::resource('division', DivisionController::class);
  
  Route::resource('vision', VisionController::class);
  Route::resource('mission', MissionController::class)->except(['index', 'show', 'create', 'edit']);

  Route::get('project-dashboard/getVisions', [ProjectDashboardController::class, 'getVisions'])->name('visions');
  Route::get('project-dashboard/getTotalCounts', [ProjectDashboardController::class, 'getTotalCounts'])->name('total-counts');
  Route::get('project-dashboard/getOverdueTasks', [ProjectDashboardController::class, 'getOverdueTasks'])->name('overdue-tasks');
  Route::get('project-dashboard/getUpcomingTasks', [ProjectDashboardController::class, 'getUpcomingTasks'])->name('upcoming-tasks');
  Route::get('project-dashboard/fetchusertask/{userId}/{filter}', [ProjectDashboardController::class, 'fetchusertask'])->name('fetchusertask');
  Route::get('project-dashboard', [ProjectDashboardController::class,'index'])->name('projectdashboard.index');

  Route::resource('daily-task-category', DailyTaskCategoryController::class);

  Route::resource('shifting-ob', ShiftingObController::class)->only(['index','store','update','destroy']);
  Route::resource('schedule-ob', ScheduleObController::class)->except(['edit','create','show']);

  Route::post('division-budget/approve/{divisionBudget}', [DivisionBudgetController::class, 'approve'])->name('division-budget.approve');
  Route::resource('division-budget', DivisionBudgetController::class);

  Route::get('inbox/unreadcount', [InboxController::class, 'unreadcount'])->name('inbox.unreadcount');
  Route::get('/inbox/{id}', [InboxController::class, 'show'])->name('inbox.show');
  Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
  
  Route::patch('letter-submission/approvement', [LetterSubmissionController::class, 'approvement'])->name('letter-submission.approvement');
  Route::resource('letter-submission', LetterSubmissionController::class);
  
  Route::resource('position', PositionController::class);
  Route::get('device', [DeviceController::class,'index'])->name('device.index');
  Route::get('device/dataJson', [DeviceController::class, 'dataJson'])->name('device.dataJson');

  Route::resource('national-holiday', NationalHolidayController::class)->only(['index','store','update','destroy']);
  
  Route::get('employee-checking/export/{format}', [EmployeeCheckingController::class, 'export'])->name('employee-checking.export');
  Route::get('employee-checking/checkExportStatus', [EmployeeCheckingController::class, 'checkExportStatus'])->name('employee-checking.checkExportStatus');
  Route::get('employee-checking/clearsession', [EmployeeCheckingController::class, 'clearsession'])->name('employee-checking.clearsession');
  Route::get('employee-checking/checkLastScheduledCheckin',[EmployeeCheckingController::class,'checkLastScheduledCheckin'])->name('employee-checking.checkLastScheduledCheckin');
  Route::put('employee-checking/updatestatus/{employee_checking}',[EmployeeCheckingController::class,'updatestatus'])->name('employee-checking.updatestatus');
  Route::resource('employee-checking', EmployeeCheckingController::class)->only(['index','update']);

  Route::resource('pass-checking', PassCheckingController::class);

  Route::post('kye/verifyemail', [KyeController::class, 'verifyemail'])->name('kye.verify.email');
  Route::patch('kye/approvement/{kye}', [KyeController::class, 'approvement'])->name('kye.approvement');
  Route::resource('kye', KyeController::class);
  
  Route::resource('warehouse', WarehouseController::class);
  Route::resource('sensor', SensorController::class);
  Route::resource('zone', ZoneController::class);
  Route::resource('rack', RackController::class);
  
  Route::post('shipping-rate/validateCsv', [ShippingRateController::class, 'validateCsv'])->name('shipping-rate.validateCsv');
  Route::post('shipping-rate/import', [ShippingRateController::class, 'import'])->name('shipping-rate.import');
  Route::get('shipping-rate/progress/{batchId}', [ShippingRateController::class, 'progress'])->name('shipping-rate.progress');
  Route::post('shipping-rate/checkDuplicate', [ShippingRateController::class, 'checkDuplicate'])->name('shipping-rate.checkDuplicate');
  Route::get('shipping-rate/dataTableJson', [ShippingRateController::class, 'dataTableJson'])->name('shipping-rate.dataTableJson');
  Route::resource('shipping-rate', ShippingRateController::class);
    
  Route::resource('provider', ProviderController::class);
  
  Route::get('city/select2', [CityController::class, 'select2'])->name('city.select2');
  Route::get('district/select2', [DistrictController::class, 'select2'])->name('district.select2');
  Route::get('wilayah/select2', [WilayahController::class, 'select2'])->name('wilayah.select2');
  
  Route::get('province/dataTableJson', [ProvinceController::class, 'dataTableJson'])->name('province.dataTableJson');
  Route::get('city/dataTableJson', [CityController::class, 'dataTableJson'])->name('city.dataTableJson');
  Route::get('district/dataTableJson', [DistrictController::class, 'dataTableJson'])->name('district.dataTableJson');
  Route::get('subdistrict/dataTableJson', [SubdistrictController::class, 'dataTableJson'])->name('subdistrict.dataTableJson');
  Route::get('postal-code/dataTableJson', [PostalCodeController::class, 'dataTableJson'])->name('postal-code.dataTableJson');
  
  Route::resources([
      'province' => ProvinceController::class,
      'city' => CityController::class,
      'district' => DistrictController::class,
      'subdistrict' => SubdistrictController::class,
      'postal-code' => PostalCodeController::class,
  ]);
  
  Route::get('shipping-calculation/detail', [ShippingCalculationController::class, 'detail'])->name('shipping-calculation.detail');
  Route::get('shipping-calculation', [ShippingCalculationController::class, 'index'])->name('shipping-calculation.index');
  Route::get('shipping-calculation/searchRates', [ShippingCalculationController::class, 'searchRates'])->name('shipping-calculation.searchRates');
  Route::get('shipping-calculation/select2Origin', [ShippingCalculationController::class, 'select2Origin'])->name('shipping-calculation.select2Origin');
  Route::get('shipping-calculation/select2Destination', [ShippingCalculationController::class, 'select2Destination'])->name('shipping-calculation.select2Destination');
  
  Route::get('ask-bos',[AskBosController::class,'index'])->name('ask-bos.index');
  Route::get('ask-bos/checkResponse', [AskBosController::class, 'checkResponse'])->name('check.response');
  Route::post('ask-bos/ask', [AskBosController::class, 'ask'])->name('ask.bos');
  Route::post('ask-bos/makeDesition', [AskBosController::class, 'makeDesition'])->name('ask.makeDesition');
  
  Route::put('decision/approvement/{id}', [DecisionController::class, 'approvement'])->name('decision.approvement');
  Route::resource('decision', DecisionController::class)->except(['create']);
  
  Route::get('partnership-agreement/share/{id}',[PartnershipAgreementController::class,'share'])->name('partnership-agreement.share');
  Route::get('partnership-agreement/downloadPdf/{id}',[PartnershipAgreementController::class,'downloadPdf'])->name('partnership-agreement.downloadPdf');
  Route::put('partnership-agreement/approvement/{id}',[PartnershipAgreementController::class,'approvement'])->name('partnership-agreement.approvement');
  Route::put('partnership-agreement/signature/{id}',[PartnershipAgreementController::class,'signature'])->name('partnership-agreement.signature');
  Route::post('partnership-agreement/submit/{id}',[PartnershipAgreementController::class,'submit'])->name('partnership-agreement.submit');
  Route::resource('partnership-agreement', PartnershipAgreementController::class);

  Route::resource('supplier-category', SupplierCategoryController::class);
  
  Route::get('product-supplier/importProgress/{batchId}', [ProductSupplierController::class, 'importProgress']);
  Route::post('product-supplier/import', [ProductSupplierController::class, 'import'])->name('product-supplier.import');
  Route::resource('product-supplier', ProductSupplierController::class);

  
  Route::get('dayoff/export/{format}', [DayoffController::class, 'export'])->name('dayoff.export');
  Route::get('dayoff/checkExportStatus', [DayoffController::class, 'checkExportStatus'])->name('dayoff.checkExportStatus');
  Route::get('dayoff/clearExportSession', [DayoffController::class, 'clearExportSession'])->name('dayoff.clearExportSession');
  Route::get('dayoff/infoApprovementHr', [DayoffController::class, 'infoApprovementHr'])->name('dayoff.infoApprovementHr');
  Route::get('dayoff/infoApprovementFinance', [DayoffController::class, 'infoApprovementFinance'])->name('dayoff.infoApprovementFinance');
  Route::get('dayoff/checkInfo', [DayoffController::class, 'checkInfo'])->name('dayoff.checkInfo');
  Route::post('dayoff/financeApprovement', [DayoffController::class, 'financeApprovement'])->name('dayoff.financeApprovement');
  Route::post('dayoff/hrApprovement', [DayoffController::class, 'hrApprovement'])->name('dayoff.hrApprovement');
  Route::resource('dayoff', DayoffController::class);

  Route::get('dashboard-weekly-report', [ReportChartController::class, 'index'])->name('dashboard-weekly-report.index');
  Route::get('dashboard-weekly-report/data', [ReportChartController::class, 'data'])->name('dasboard.weekly-report.fetch');
  
  Route::get('weekly-report/reminderDashboard', [WeeklyReportController::class, 'reminderDashboard'])->name('weekly-report.reminderDashboard');
  Route::resource('weekly-report', WeeklyReportController::class);
  
  Route::get('vehicle/infoPic', [VehicleController::class, 'infoPic'])->name('reminder.vehicle.pic');
  Route::get('vehicle/infoManager', [VehicleController::class, 'infoManager'])->name('reminder.vehicle.manager');
  Route::resource('vehicle', VehicleController::class);
  
  Route::get('subscribe-letter/infoPic', [SubscribeLetterController::class, 'infoPic'])->name('reminder.letter.pic');
  Route::get('subscribe-letter/infoManager', [SubscribeLetterController::class, 'infoManager'])->name('reminder.letter.manager');
  Route::resource('subscribe-letter', SubscribeLetterController::class);
  
  Route::resource('flowchart', FlowChartController::class);
  
  Route::resource('chat-message', ChatMessageController::class)->only(['store','show']);
  
  Route::get('item-request/workflow/{id}', [ItemRequestController::class, 'workflow'])->name('item-request.workflow');
  Route::get('item-request/dataTableJson', [ItemRequestController::class, 'dataTableJson'])->name('item-request.datatable');
  Route::post('item-request/fetchProductSupplier', [ItemRequestController::class, 'fetchProductSupplier'])->name('item-request.fetch-suppliers');
  Route::post('item-request/closed/{id}', [ItemRequestController::class, 'closed'])->name('item-request.closed');
  Route::put('item-request/delivery/{id}', [ItemRequestController::class, 'delivery'])->name('item-request.delivery');
  Route::resource('item-request', ItemRequestController::class);
  
  Route::post('item-purchase/complete/{id}', [ItemPurchaseController::class, 'complete'])->name('item-purchase.complete');
  Route::put('item-purchase/payment/{id}', [ItemPurchaseController::class, 'payment'])->name('item-purchase.payment');
  Route::resource('item-purchase', ItemPurchaseController::class)->only(['store','update']);  

  Route::post('meeting/join', [MeetingController::class, 'join'])->name('meeting.join');
  Route::resource('meeting', MeetingController::class);

  Route::put('mom/storeAgenda/{id}', [MomController::class,'storeAgenda'])->name('mom.storeAgenda');
  Route::put('mom/updateAgenda/{id}', [MomController::class,'updateAgenda'])->name('mom.updateAgenda');
  Route::delete('mom/deleteAgenda/{momAgenda}', [MomController::class,'deleteAgenda'])->name('mom.deleteAgenda');

  Route::delete('mom/deleteTask/{momTask}', [MomController::class,'deleteTask'])->name('mom.deleteTask');
  Route::post('mom/approveExternalTask/task/{token}', [MomController::class, 'approveExternalTask'])->name('external.task.approve');
  Route::put('mom/storeTask/{id}', [MomController::class,'storeTask'])->name('mom.storeTask');
  Route::put('mom/updateTask/{id}', [MomController::class,'updateTask'])->name('mom.updateTask');
  
  Route::resource('mom', MomController::class);

  Route::delete('used-laptop/mediaDestroy/{id}', [UsedLaptopController::class,'mediaDestroy'])->name('used-laptop.media.destroy');
  Route::patch('used-laptop/maskAsSold/{slug}', [UsedLaptopController::class,'maskAsSold'])->name('used-laptop.mark-as-sold');
  Route::post('used-laptop/checkSerialNumber', [UsedLaptopController::class, 'checkSerialNumber'])->name('used-laptop.check-serial');
  Route::resource('used-laptop', UsedLaptopController::class);

  Route::resource('master-check-item', MasterCheckItemController::class)->only(['index', 'store', 'update', 'destroy']);
  
  Route::delete('used-item/mediaDestroy/{id}', [UsedItemController::class,'mediaDestroy'])->name('used-item.media.destroy');
  Route::patch('used-item/maskAsSold/{slug}', [UsedItemController::class,'maskAsSold'])->name('used-item.mark-as-sold');
  Route::resource('used-item', UsedItemController::class);
  
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
  
  Route::get('internet-package', InternetPackageIndex::class)->name('internet-package.index');
  Route::get('internet-package/create', InternetPackageForm::class)->name('internet-package.create');
  Route::get('internet-package/edit/{id}', InternetPackageForm::class)->name('internet-package.edit');

  Route::get('internet-customer', InternetCustomerIndex::class)->name('internet-customer.index');
  Route::get('internet-customer/edit/{id}', InternetCustomerForm::class)->name('internet-customer.edit');
  Route::get('internet-customer/{customerId}', InternetCustomerShow::class)->name('internet-customer.show');
  
  Route::get('promo', PromoIndex::class)->name('promo.index');
  Route::get('promo/create', PromoForm::class)->name('promo.create');
  Route::get('promo/edit/{id}', PromoForm::class)->name('promo.edit');
  
  Route::get('webhook-setting', WebhookSettingTable::class)->name('webhook-setting.index');
  
  // Barcode 
  Route::get('barcode', [BarcodeAttendanceController::class, 'index'])->name('barcode.index');
  Route::post('barcode/generate', [BarcodeAttendanceController::class, 'generate'])->name('barcode.generate');
  
  // Scan Barcode
  Route::get('office-attendance/export', [OfficeAttendanceController::class, 'export'])->name('office_attendance.export');
  Route::get('office-attendance', [OfficeAttendanceController::class, 'index'])->name('office-attendance.index');
  Route::get('office-attendance/scan/{code}', [OfficeAttendanceController::class, 'scan'])->name('office-attendance.scan');
  
  // Lengkapi data absen (foto + lokasi)
  Route::put('office-attendance/complete/{code}', [OfficeAttendanceController::class, 'complete'])->name('office-attendance.complete');
  
  Route::get('supplier-type', ProductSupplierTypeIndex::class)->name('supplier-type.index');

  Route::get('brand-product-store', BrandProductStoreIndex::class)->name('brand-product-store.index');
  
  Route::get('category-product-store', CategoryProductStoreIndex::class)->name('category-product-store.index');

  Route::get('product-store/print', ProductStorePrint::class)->name('product-store.print');
  Route::get('product-store/create', ProductStoreForm::class)->name('product-store.create');
  Route::get('product-store/edit/{id}', ProductStoreForm::class)->name('product-store.edit');
  Route::get('product-store/{id}', ProductStoreShow::class)->name('product-store.show');
  Route::get('product-store', ProductStoreIndex::class)->name('product-store.index');
  Route::get('product-store/print', ProductStorePrint::class)->name('product-store.print');
  
  Route::get('punishment-user', PunishmentUserTable::class)->name('punishment-user.index');

  Route::get('sales', \App\Http\Livewire\Sale\SaleIndex::class)->name('sales.index');
  Route::get('sales/{id}', \App\Http\Livewire\Sale\SaleShow::class)->name('sales.show');
  
  Route::get('store-selling', [SaleController::class, 'index'])->name('store-selling.index');
  Route::post('store-selling/sendReceiptByEmail', [SaleController::class, 'sendReceiptByEmail'])->name('store-selling.sendReceiptByEmail');
  Route::post('store-selling/searchProduct', [SaleController::class, 'searchProduct'])->name('store-selling.searchProduct');
  Route::post('store-selling/processPayment', [SaleController::class, 'processPayment'])->name('store-selling.processPayment');
  Route::post('store-selling/saveDraft', [SaleController::class, 'saveDraft'])->name('store-selling.saveDraft');
  Route::get('store-selling/loadDraft/{draft}', [SaleController::class, 'loadDraft'])->name('store-selling.loadDraft');
  Route::delete('store-selling/deleteDraft/{draft}', [SaleController::class, 'deleteDraft'])->name('store-selling.deleteDraft');
  Route::get('store-selling/printReceipt/{sale}', [SaleController::class, 'printReceipt'])->name('store-selling.printReceipt');
  Route::get('store-selling/drafts', [SaleController::class, 'getDrafts'])->name('store-selling.drafts');
});

  Route::get('internet-customer/registration/{companyId}', InternetCustomerForm::class)->name('internet-customer.create');
  Route::get('internet-customer/customer/{code}', CustomerShow::class)->name('internet-customer.customer.show');
  
// Route::middleware(['auth'])->group(function () {
// });

Route::get('error/{code?}', function ($code = 500) {
    return view('public_error', [
        'code' => $code,
        'title' => session('title', 'Terjadi Kesalahan'),
        'message' => session('message', 'Kesalahan tidak diketahui'),
        'icon' => session('icon', 'fas fa-exclamation-triangle'),
    ]);
})->name('public.error');

Route::post('bos-ticket', [TicketController::class,'store'])->name('bos-ticket.store');
Route::get('bos-ticket', [TicketController::class,'create'])->name('bos-ticket.create');;

Route::get('/{slug}',[SortUrlController::class,'index'])->name('download.index');



