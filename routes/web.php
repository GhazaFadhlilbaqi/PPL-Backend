<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Models\PasswordReset;
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

// NOTE: Debug only route !
Route::get('/forgot-password', function() {
    return view('mails.auth.forgot-password', [
        'passwordReset' => PasswordReset::first(),
    ]);
});

Route::get('/auth/email-verification/confirm/{token}', [RegisterController::class, 'confirmEmail'])->name('register.confirm_email');
