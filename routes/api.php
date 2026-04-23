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
use App\Http\Controllers\API\MomApiController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\API\DailyTaskMobileController;
use App\Http\Controllers\API\ItemRequestMobileController;
use App\Http\Controllers\API\ItemPurchaseMobileController;
use App\Http\Controllers\API\FlowChartController;
use App\Http\Controllers\API\RegionController;
use App\Http\Controllers\API\InternetCustomerController;
use App\Http\Controllers\API\UserApiController;

use App\Http\Controllers\API\ProductStoreController;
use App\Http\Controllers\API\MeetingApiController;
use App\Http\Controllers\API\InternetCustomerApiController;
use App\Http\Controllers\API\EkycController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\API\QrScanApiController;



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

Route::post('login_flutter', [LoginController::class, 'login_flutter']);

Route::post('n8n/ktp/result', [EkycController::class, 'receiveKtpResult']);

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


    //Mobile

        //QR Api
    Route::get('used-laptops/detail/{slug}', [QrScanApiController::class, 'getUsedLaptopDetail']);
    Route::get('used-items/detail/{slug}', [QrScanApiController::class, 'getUsedItemDetail']);
    Route::get('product-stores/detail/{code}', [QrScanApiController::class, 'getProductStoreDetail']);
    Route::get('internet-customers/detail/{code}', [QrScanApiController::class, 'getInternetCustomerDetail']);
    Route::get('quotes/detail-pdf/{quote_number}', [QrScanApiController::class, 'getQuotationPdf'])
    ->where('quote_number', '.*');

    Route::prefix('tasks')->group(function() {
        Route::get('today', [DailyTaskMobileController::class, 'indexToday']);
        Route::get('tomorrow', [DailyTaskMobileController::class, 'indexTomorrow']);
        Route::get('overdue', [DailyTaskMobileController::class, 'indexOverdue']);
        Route::get('user/{userId}', [DailyTaskMobileController::class, 'indexTaskByUser'])->name('tasks.user.mobile');
        Route::get('division/{divisionId}', [DailyTaskMobileController::class, 'indexTaskByDivision']);
        
        Route::put('statuschange/{slug}', [DailyTaskMobileController::class, 'statusChange'])->name('tasks.statuschange.mobile'); 
        Route::post('{slug}/report', [DailyTaskMobileController::class, 'report'])->name('tasks.report.mobile');
        Route::post('{slug}/update-media', [DailyTaskMobileController::class, 'updateMedia'])->name('tasks.updateMedia.mobile');
        Route::delete('media/{id}', [DailyTaskMobileController::class, 'deleteMedia'])->name('tasks.deleteMedia.mobile');
        Route::post('approval/{slug}', [DailyTaskMobileController::class, 'approval'])->name('api.dailytask.approval');
        Route::resource('/', DailyTaskMobileController::class)->only(['index', 'show', 'store', 'update', 'destroy'])
            ->parameters(['' => 'task']); 
    });

    Route::prefix('daily-task-projects')->group(function() {
        Route::get('/', [DailyTaskMobileController::class, 'indexDailyTaskProjects']); 
        Route::get('titles', [DailyTaskMobileController::class, 'indexProjects']); 
        Route::get('categories', [DailyTaskMobileController::class, 'indexDailyTaskCategories']); 
        Route::get('types', [DailyTaskMobileController::class, 'indexDailyTaskTypes']);
        Route::get('objectives', [DailyTaskMobileController::class, 'indexDailyTaskObjectives']);
        Route::get('keyresults/{objectiveId}', [DailyTaskMobileController::class, 'indexKeyResults']);
        Route::get('users', [DailyTaskMobileController::class, 'indexDailyTaskUsers']);
        Route::get('statuses', [DailyTaskMobileController::class, 'indexTaskStatuses']); 
    });

    Route::prefix('dailytasks')->group(function() {
        Route::get('summary', [HomeController::class, 'indexSummary']);
        Route::get('divisions', [DailyTaskMobileController::class, 'indexDivision']);
        Route::get('check-quota', [DailyTaskMobileController::class, 'checkDivisionQuota']);
        Route::post('generate-media-url', [DailyTaskMobileController::class, 'generateMediaUrl'])->name('medias.generateMediaUrl.mobile');
        Route::get('users-by-division/{divisionId}', [DailyTaskMobileController::class, 'getUsersByDivision'])->name('users.division.mobile');
    });


    Route::get('users/division/{divisionId}', [DailyTaskMobileController::class, 'getUsersByDivision'])
        ->name('users.division.mobile');
    Route::get('users', [UserApiController::class, 'indexUsers']);
    Route::get('main-projects', [UserApiController::class, 'indexMainProjects']);
    Route::get('projects', [UserApiController::class, 'indexProjects']);


    
    Route::get('tasks/user/{userId}', [DailyTaskMobileController::class, 'indexTaskByUser'])
        ->name('tasks.user.mobile');

    Route::get('tasks/today', [DailyTaskMobileController::class, 'indexToday']);

    Route::get('tasks/tomorrow', [DailyTaskMobileController::class, 'indexTomorrow']);
    Route::get('tasks/overdue', [DailyTaskMobileController::class, 'indexOverdue']);

    Route::put('tasks/statuschange/{slug}', [DailyTaskMobileController::class, 'statusChange'])
    ->name('tasks.statuschange.mobile'); 
    Route::post('tasks/{slug}/report', [DailyTaskMobileController::class, 'report'])
    ->name('tasks.report.mobile');
    Route::post('tasks/{slug}/update-media', [DailyTaskMobileController::class, 'updateMedia'])
    ->name('tasks.updateMedia.mobile');
    Route::delete('tasks/media/{id}', [DailyTaskMobileController::class, 'deleteMedia'])
        ->name('tasks.deleteMedia.mobile');

    Route::resource('tasks', DailyTaskMobileController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    
    Route::get('home/daily-task-summary', [HomeController::class, 'indexSummary']);
    Route::get('tasks/division/{divisionId}', [DailyTaskMobileController::class, 'indexTaskByDivision']);

    Route::get('task-statuses', [DailyTaskMobileController::class, 'indexTaskStatuses']); 
    Route::get('daily-task-projects', [DailyTaskMobileController::class, 'indexDailyTaskProjects']); 
    Route::get('daily-task-projects-title', [DailyTaskMobileController::class, 'indexProjects']); 
    Route::get('daily-task-categories', [DailyTaskMobileController::class, 'indexDailyTaskCategories']); 
    Route::get('daily-task-types', [DailyTaskMobileController::class, 'indexDailyTaskTypes']);
    Route::get('daily-task-objectives', [DailyTaskMobileController::class, 'indexDailyTaskObjectives']);
    Route::get('daily-task-keyresults/{objectiveId}', [DailyTaskMobileController::class, 'indexKeyResults']);
    Route::get('daily-task-users', [DailyTaskMobileController::class, 'indexDailyTaskUsers']);
    Route::post('/tasks/approval/{slug}', [DailyTaskMobileController::class, 'approval'])
        ->name('api.dailytask.approval');
    Route::post('medias/generate-media-url', [DailyTaskMobileController::class, 'generateMediaUrl'])
        ->name('medias.generateMediaUrl.mobile');
    Route::get('divisions', [DailyTaskMobileController::class, 'indexDivision']);
    Route::get('divisions/check-division-quota', [DailyTaskMobileController::class, 'checkDivisionQuota']);
    Route::get('/item-requests/{id}/workflow', [ItemRequestMobileController::class, 'workflow']);
    Route::post('item-requests/{id}/add-vendor', [ItemPurchaseMobileController::class,'addVendor']);
    Route::post('item-requests/{id}/delivery', [ItemRequestMobileController::class,'delivery']);
    Route::get('item-requests/{id}/delivery-detail', [ItemRequestMobileController::class, 'getDelivery']);
    Route::get('item-requests/company', [ItemRequestMobileController::class, 'loadByCompany']);

    Route::get('item-requests/categories', [ItemRequestMobileController::class, 'categories']);
    Route::get('item-requests/types', [ItemRequestMobileController::class, 'types']);
    Route::get('item-requests/sprinters', [ItemRequestMobileController::class, 'sprinters']);
    Route::get('item-requests/product-suppliers', [ItemRequestMobileController::class, 'productSuppliers']);

    Route::resource('item-requests', ItemRequestMobileController::class)
    ->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::post('item-purchases/{id}/payment', [ItemPurchaseMobileController::class,'payment']);
    Route::get('item-purchases/{item_request_id}/get-payment', [ItemPurchaseMobileController::class, 'getPayment'])
        ->name('item-purchases.getPayment');
        
    Route::post('item-purchases/{id}/closed', [ItemPurchaseMobileController::class, 'closed']);
    Route::post('item-purchases/{id}/complete', [ItemPurchaseMobileController::class, 'complete']);

    Route::resource('item-purchases', ItemPurchaseMobileController::class)
    ->only(['store', 'update']);

    // Product Store Search API
    Route::get('product-stores/search', [ProductStoreController::class, 'search'])->name('api.product-stores.search');
    
    Route::apiResource('flowcharts', FlowChartController::class);

    // CRUD Meeting
    Route::apiResource('meeting', MeetingApiController::class);

    // Internet Customer
    Route::prefix('internet-customers')->controller(InternetCustomerApiController::class)->group(function () {
        Route::get('/', 'index');                          
        Route::get('/{id}', 'show');                       
        Route::post('/{id}/approve', 'approve');           
        Route::post('/{id}/close', 'close');               
        Route::post('/{id}/complete-installation', 'completeInstallation');
        Route::get('/{id}/installation-resources', 'getInstallationResources');
        Route::get('/get-ip-pools/by-router','getIpPoolsByRouter');
    });

    // SKAM Import API
    Route::post('internet-customer/import', [InternetCustomerController::class, 'import']);
    
});


Route::group(['middleware' => ['auth:api']], function() 
{
    Route::post('provision', [MikrotikController::class, 'provision']);   
    Route::post('cut', [MikrotikController::class, 'cut']);
    Route::post('restore', [MikrotikController::class, 'restore']);

    Route::resource('dailytask', DailyTaskController::class);
    Route::put('dailytask/statuschange/{slug}', [DailyTaskController::class,'statuschange'])->name('dailytask.statuschange');
    Route::put('dailytask/report/{slug}', [DailyTaskController::class, 'report']);

    Route::get('daily_task_project/getcustomfield/{project}', [DailyTaskProjectController::class,'getcustomfield'])->name('getcustomfield');

    Route::get('objective/getresult/{objective}', [ObjectiveController::class,'getresult'])->name('getresult');
    // Route::post('dailytask/{slug}/approve', [DailyTaskController::class, 'approvement']);
    // Route::post('dailytask/{slug}/extend', [DailyTaskController::class, 'extend']);
    Route::patch('used-laptop/maskAsSold/{id}', [UsedLaptopController::class,'maskAsSold'])->name('used-laptop.maskAsSold');


    Route::resource('mom', MomApiController::class);
    Route::prefix('mom')->group(function () {

        // MOM Task Routes
        Route::post('/{id}/tasks', [MomApiController::class, 'storeTask']); // POST - Create task
        Route::put('/tasks/{id}', [MomApiController::class, 'updateTask']); // PUT - Update task
        Route::delete('/tasks/{id}', [MomApiController::class, 'deleteTask']); // DELETE - Delete task

        // MOM Agenda Routes
        Route::post('/{id}/agendas', [MomApiController::class, 'storeAgenda']); // POST - Create agenda
        Route::put('/agendas/{id}', [MomApiController::class, 'updateAgenda']); // PUT - Update agenda
        Route::delete('/agendas/{id}', [MomApiController::class, 'deleteAgenda']); // DELETE - Delete agenda
    });

    Route::post('test', function (Request $request) {
        \Illuminate\Support\Facades\Log::info($request->all());
    });
});

Route::group(['middleware' => ['auth:api']], function() 
{
    Route::post('/flutter/broadcast/auth', function (Request $request) {
        return Broadcast::auth($request);
    });
    Route::post('/user/fcm-token', [UserController::class, 'saveFcmToken']);
    Route::get('/user/role-division', [UserController::class, 'getRoleAndDivision']);
    Route::post('logout', [LoginController::class, 'logout']);
});

 Route::prefix('region')->group(function () {
    Route::get('/getCountries', [RegionController::class, 'getCountries']);
    Route::get('/getProvinces', [RegionController::class, 'getProvinces']);
    Route::get('/getCities', [RegionController::class, 'getCities']);
    Route::get('/getDistricts', [RegionController::class, 'getDistricts']);
    Route::get('/getSubdistricts', [RegionController::class, 'getSubdistricts']);
});
