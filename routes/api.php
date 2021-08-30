<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\User\UserController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function() {

    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('forgot-password', [ForgotPas])

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
    Route::post('fetch-snap-token', [PaymentController::class, 'fetchSnapToken']);
    // NOTE: For demo purpose only
    Route::post('demo-add-token', [PaymentController::class, 'addToken']);
});
