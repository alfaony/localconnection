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
use App\Http\Controllers\API\DailyTaskController;
use App\Http\Controllers\API\ObjectiveController;
use App\Http\Controllers\API\DailyTaskProjectController;
use App\Http\Controllers\API\UsedLaptopController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\API\DailyTaskMobileController;
use App\Http\Controllers\API\ItemRequestMobileController;
use App\Http\Controllers\API\ItemPurchaseMobileController;


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

    Route::resource('dailytask', DailyTaskController::class);
    Route::put('dailytask/statuschange/{slug}', [DailyTaskController::class,'statuschange'])->name('dailytask.statuschange');
    Route::put('dailytask/report/{slug}', [DailyTaskController::class, 'report']);

    Route::get('daily_task_project/getcustomfield/{project}', [DailyTaskProjectController::class,'getcustomfield'])->name('getcustomfield');

    Route::get('objective/getresult/{objective}', [ObjectiveController::class,'getresult'])->name('getresult');
    // Route::post('dailytask/{slug}/approve', [DailyTaskController::class, 'approvement']);
    // Route::post('dailytask/{slug}/extend', [DailyTaskController::class, 'extend']);
    Route::patch('used-laptop/maskAsSold/{id}', [UsedLaptopController::class,'maskAsSold'])->name('used-laptop.maskAsSold');


    //Mobile
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
    Route::resource('item-requests', ItemRequestMobileController::class)
    ->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::post('item-purchases/{id}/payment', [ItemPurchaseMobileController::class,'payment']);
    Route::resource('item-purchases', ItemPurchaseMobileController::class)
    ->only(['store', 'update']);

});

Route::group(['middleware' => ['auth:api']], function() 
{
    Route::post('logout', [LoginController::class, 'logout']);
});