<?php

use App\Exports\SCurveExport;
use App\Http\Controllers\Admin\AdminProjectController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\CustomAhpController;
use App\Http\Controllers\CustomAhsController;
use App\Http\Controllers\CustomAhsItemController;
use App\Http\Controllers\CustomItemPriceController;
use App\Http\Controllers\CustomItemPriceGroupController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\ImplementationScheduleController;
use App\Http\Controllers\Master\AhpController;
use App\Http\Controllers\Master\AhsController;
use App\Http\Controllers\Master\AhsItemController;
use App\Http\Controllers\Master\ItemPriceController;
use App\Http\Controllers\Master\ItemPriceGroupController;
use App\Http\Controllers\Master\MasterRabCategoryController;
use App\Http\Controllers\Master\MasterRabItemHeaderController;
use App\Http\Controllers\Master\ProvinceController;
use App\Http\Controllers\Master\RabController as MasterRabController;
use App\Http\Controllers\Master\RabItemController as MasterRabItemController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RabController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\RabItemController;
use App\Http\Controllers\RabItemHeaderController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TutorialController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware(('auth:sanctum'))->group(function() {
    Route::resource('users', UserController::class);
    Route::put('users/{user}/demo-quota', [UserController::class, 'updateDemoQuota']);

    Route::prefix('admin')->group(function() {
        Route::resource('projects', AdminProjectController::class);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('order')->group(function() {
    Route::post('notify', [OrderController::class, 'notify']);

    Route::middleware('auth:sanctum')->group(function() {
        Route::get('', [OrderController::class, 'index']);
        Route::post('by-project/{project}', [OrderController::class, 'orderStatusByProject']);
    });
});

Route::prefix('auth')->group(function() {

    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendConfirmationMail']);

    Route::prefix('reset-password')->group(function() {
        Route::post('verify-token', [ForgotPasswordController::class, 'verifyResetToken']);
        Route::post('{token}', [ForgotPasswordController::class, 'resetPassword']);
    });

    Route::middleware('auth:sanctum')->group(function() {
        Route::post('logout', [LoginController::class, 'logout']);
        Route::post('verify', [LoginController::class, 'verify']);
    });
});

Route::prefix('user')->middleware('auth:sanctum')->group(function() {
    Route::get('{user}', [UserController::class, 'show']);
    Route::post('{user}', [UserController::class, 'update']);
});

Route::prefix('payment')->middleware('auth:sanctum')->group(function() {
    # NOTE: Clean fetch-snap-token route
    Route::post('fetch-snap-token', [PaymentController::class, 'fetchSnapToken']);
    Route::post('fetch-subscription-snap-token', [PaymentController::class, 'fetchSubscriptionSnapToken'])->middleware('ensure.demo.eligibility');
    Route::post('set-canceled', [PaymentController::class, 'setCanceled']);
    Route::post('set-pending', [PaymentController::class, 'setPending']);
    # NOTE: For demo purpose only
    Route::post('demo-add-token', [PaymentController::class, 'addToken']);
});

Route::prefix('company')->middleware('auth:sanctum')->group(function() {
    Route::get('', [CompanyController::class, 'index']);
    # Because user can only have one company in one account, so store should only works for first time
    Route::post('', [CompanyController::class, 'store'])->middleware(['utils.determine-request-data-owner', 'company.ensure-user-dont-have-company']);
    Route::post('{company}', [CompanyController::class, 'update']);
});

/**
 * ----------------------
 * Master Data API
 * ----------------------
 */
Route::prefix('master')->middleware(['auth:sanctum'])->group(function() {

    Route::prefix('provinces')->middleware('auth:sanctum')->group(function() {
        Route::get('', [ProvinceController::class, 'index']);
    });

    Route::prefix('unit')->group(function() {
        Route::post('', [UnitController::class, 'store']);
        Route::get('', [UnitController::class, 'index']);
        Route::get('{unit}/delete', [UnitController::class, 'destroy']);
        Route::post('{unit}', [UnitController::class, 'update']);
    });

    Route::prefix('item-price-group')->group(function() {
        Route::get('', [ItemPriceGroupController::class, 'index']);
        Route::post('', [ItemPriceGroupController::class, 'store']);
        Route::get('{itemPriceGroup}/delete', [ItemPriceGroupController::class, 'destroy']);
        Route::post('{itemPriceGroup}', [ItemPriceGroupController::class, 'update']);
    });

    Route::prefix('item-price')->group(function() {
        Route::post('', [ItemPriceController::class, 'store']);
        Route::get('', [ItemPriceController::class, 'index']);
        Route::get('export', [ItemPriceController::class, 'export']);
        Route::post('import', [ItemPriceController::class, 'import']);
        Route::post('{itemPriceId}', [ItemPriceController::class, 'update']);
        Route::post('{itemPrice}/batch-update', [ItemPriceController::class, 'batchUpdatePrice']);
        Route::post('{itemPrice}/delete', [ItemPriceController::class, 'destroy']);
        Route::post('{itemPrice}/set-price', [ItemPriceController::class, 'setPrice']);
    });

    Route::prefix('ahs')->group(function() {
        Route::get('ids', [AhsController::class, 'getAhsIds']);
        Route::get('export', [AhsController::class, 'export']);
        Route::post('import', [AhsController::class, 'import']);
        Route::get('project', [AhsController::class, 'fetchMasterAhsProject']);
        Route::get('{ahsId?}', [AhsController::class, 'index']);
        Route::post('{ahsId?}', [AhsController::class, 'store']);
        Route::post('{ahs}/update', [AhsController::class, 'update']);
        Route::get('{ahs}/delete', [AhsController::class, 'destroy']);
    });

    Route::prefix('ahs-item')->group(function() {
        Route::get('itemable-ids', [AhsItemController::class, 'getAhsItemableId']);
        Route::get('{ahs}', [AhsItemController::class, 'index']);
        Route::post('{ahs}', [AhsItemController::class, 'store']);
        Route::get('{ahsItem}/delete', [AhsItemController::class, 'destroy']);
        Route::post('{ahsItem}/update', [AhsItemController::class, 'update']);
    });

    Route::prefix('ahp')->group(function() {
        Route::get('', [AhpController::class, 'index']);
        Route::post('', [AhpController::class, 'store']);
        Route::get('{ahp}/delete', [AhpController::class, 'destroy']);
        Route::post('{ahp}', [AhpController::class, 'update']);
    });

    Route::prefix('rab')->group(function() {
        Route::get('', [MasterRabController::class, 'index']);
        Route::post('', [MasterRabController::class, 'store']);
        Route::get('{masterRab}', [MasterRabController::class, 'show']);
        Route::post('{masterRab}', [MasterRabController::class, 'update']);
        Route::get('{masterRab}/delete', [MasterRabController::class, 'destroy']);

        Route::prefix('{masterRab}/item')->group(function() {
            Route::post('', [MasterRabItemController::class, 'store']);
            Route::post('{masterRabItem}', [MasterRabItemController::class, 'update']);
            Route::get('{masterRabItem}/delete', [MasterRabItemController::class, 'destroy']);
        });

        Route::prefix('{masterRab}/item-header')->group(function() {
            Route::get('', [MasterRabItemHeaderController::class, 'index']);
            Route::post('', [MasterRabItemHeaderController::class, 'store']);
            Route::post('{masterRabItemHeader}', [MasterRabItemHeaderController::class, 'update']);
            Route::get('{masterRabItemHeader}/delete', [MasterRabItemHeaderController::class, 'destroy']);
        });
    });

    Route::prefix('master-rab-categories')->group(function() {
        Route::get('', [MasterRabCategoryController::class, 'index']);
        Route::post('', [MasterRabCategoryController::class, 'store']);
        Route::get('{masterRabCategory}', [MasterRabCategoryController::class, 'show']);
        Route::get('{masterRabCategory}/delete', [MasterRabCategoryController::class, 'destroy']);
    });
});


/**
 * ----------------------
 * Project API
 * ----------------------
 */
Route::prefix('project')->middleware(['auth:sanctum', 'can:access-project-page'])->group(function() {
    Route::get('', [ProjectController::class, 'index']);
    Route::post('', [ProjectController::class, 'store_demo'])->middleware('ensure.demo.eligibility');
    Route::post('{project}', [ProjectController::class, 'update']);
    Route::get('{project}', [ProjectController::class, 'show']);
    Route::get('{project}/delete', [ProjectController::class, 'destroy']);
    Route::get('{project}/update-last-opened-at', [ProjectController::class, 'updateLastOpenedAt'])->middleware(['project.ensure-project-belonging']);

    /**
     * ----------------------
     * PROJECT BASED ROUTE  |
     * ----------------------
     */
    Route::prefix('{project}')->middleware(['auth:sanctum', 'project.ensure-project-belonging', 'project.subscription.limitation.guard'])->group(function() {

        Route::get('export', [ProjectController::class, 'export'])->middleware('project.ensure-project-eligible-to-export');
        Route::post('renew', [ProjectController::class, 'renew']);
        Route::get('material-summary', [ProjectController::class, 'getMaterialSummary']);

        Route::prefix('rab')->group(function() {
            Route::get('', [RabController::class, 'index']);
            Route::post('', [RabController::class, 'store']);
            Route::get('{rab}/delete', [RabController::class, 'destroy']);
            Route::post('{rab}', [RabController::class, 'update']);

            Route::prefix('{rab}/item')->group(function() {
                Route::get('', [RabItemController::class, 'index']);
                Route::post('', [RabItemController::class, 'store']);
                Route::get('{rabItem}/delete', [RabItemController::class, 'destroy']);
                Route::post('{rabItem}', [RabItemController::class, 'update'])->middleware('request.strip-empty-char-on-request:unit_id,volume');
                Route::post('{rabItem}/ahs', [RabItemController::class, 'updateAhs']);
            });

            Route::prefix('{rab}/item-header')->group(function() {
                Route::get('', [RabItemHeaderController::class, 'index']);
                Route::post('', [RabItemHeaderController::class, 'store']);
                Route::post('{rabItemHeader}', [RabItemHeaderController::class, 'update']);
                Route::get('{rabItemHeader}/delete', [RabItemHeaderController::class, 'destroy']);
            });
        });

        Route::prefix('implementation-schedules')->group(function() {
            Route::get('implementation-schedule-duration', [ImplementationScheduleController::class, 'getProjectDuration']);
            Route::get('', [ImplementationScheduleController::class, 'index']);
            Route::post('', [ImplementationScheduleController::class, 'update']);
            Route::get('{implementationSchedule}/delete', [ImplementationScheduleController::class, 'destroy']);
            Route::post('update-project-duration', [ImplementationScheduleController::class, 'updateProjectDuration']);
            Route::get('download-s-curve', [ImplementationScheduleController::class, 'downloadSCurve']);
            Route::get('show-s-curve', [ImplementationScheduleController::class, 'showSCurveContent']);
            Route::delete('{rabItem}', [ImplementationScheduleController::class, 'destroy']);
        });

        Route::prefix('custom-item-price-group')->group(function() {
            Route::get('', [CustomItemPriceGroupController::class, 'index']);
            Route::post('', [CustomItemPriceGroupController::class, 'store']);
            Route::get('query', [CustomItemPriceGroupController::class, 'query']);
            Route::get('{customItemPriceGroup}/delete', [CustomItemPriceGroupController::class, 'destroy']);
            Route::post('{customItemPriceGroup}', [CustomItemPriceGroupController::class, 'update']);
        });

        Route::prefix('custom-item-price')->group(function() {
            Route::get('', [CustomItemPriceController::class, 'index']);
            Route::post('', [CustomItemPriceController::class, 'store']);
            Route::post('{customItemPrice}', [CustomItemPriceController::class, 'update']);
            Route::get('{customItemPrice}/delete', [CustomItemPriceController::class, 'destroy'])->middleware('custom-item-price.protect-default-model');
        });

        Route::prefix('custom-ahp')->group(function() {
            Route::get('', [CustomAhpController::class, 'index']);
            Route::post('', [CustomAhpController::class, 'store']);
            Route::post('{customAhp}', [CustomAhpController::class, 'update'])->middleware('custom-ahp.protect-default-model');
            Route::get('{customAhp}/delete', [CustomAhpController::class, 'destroy'])->middleware('custom-ahp.protect-default-model');
            Route::get('query', [CustomAhpController::class, 'query']);
        });

        Route::prefix('custom-ahs')->group(function() {
            Route::get('', [CustomAhsController::class, 'index']);
            Route::post('', [CustomAhsController::class, 'store']);
            Route::post('{customAhs}', [CustomAhsController::class, 'update']);
            Route::get('{customAhs}/delete', [CustomAhsController::class, 'destroy']);
            Route::get('ids', [CustomAhsController::class, 'getAhsIds']);
            Route::get('query', [CustomAhsController::class, 'query']);
        });

        Route::prefix('custom-ahs-item')->group(function() {
            Route::post('', [CustomAhsItemController::class, 'store']);
            Route::get('itemable-ids', [CustomAhsItemController::class, 'getCustomAhsItemableId']);
            Route::get('{customAhsItem}/delete', [CustomAhsItemController::class, 'destroy']);
            Route::post('{customAhsItem}', [CustomAhsItemController::class, 'update']);
        });

    });

});

Route::prefix('tutorials')->middleware('auth:sanctum')->group(function() {
    Route::get('', [TutorialController::class, 'index'])->name('tutorials');
    Route::post('update', [TutorialController::class, 'update'])->name('tutorials.update');
});

Route::prefix('subscriptions')->middleware('auth:sanctum')->group(function() {
    Route::get('', [SubscriptionController::class, 'index']);
});

Route::prefix('features')->middleware('auth:sanctum')->group(function() {
    Route::resource('', FeaturesController::class);
});

Route::prefix('debug')->middleware('protect-debug')->group(function() {
    Route::post('send-dummy-mail', [DebugController::class, 'sendDummyMail']);
    Route::post('clear', [DebugController::class, 'clear']);
});
