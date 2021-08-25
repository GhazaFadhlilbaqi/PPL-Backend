<?php

use App\Http\Controllers\Auth\RegisterController;
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
    if (config('app.disable_homepage')) return abort(404);
    else return view('welcome');
});

Route::get('/auth/email-verification/confirm/{token}', [RegisterController::class, 'confirmEmail'])->name('register.confirm_email');
