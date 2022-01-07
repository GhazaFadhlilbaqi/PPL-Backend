<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

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
    return config('app.disable_homepage') ? abort(404) : view('welcome');
});

// NOTE: Just for testing only !
Route::get('/project/{project}/export', [ProjectController::class, 'export']);

Route::get('/auth/email-verification/confirm/{token}', [RegisterController::class, 'confirmEmail'])->name('register.confirm_email');
