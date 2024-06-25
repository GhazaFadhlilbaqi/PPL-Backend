<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

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

    $passwordReset = PasswordReset::first();

    return view('mails.auth.email-verification', [
        'user' => User::where('email', $passwordReset->email)->first(),
        'token' => 'sdas',
        'passwordReset' => $passwordReset,
    ]);
});

Route::get('populate-user', function() {
    $user1 = User::create([
        'first_name' => 'Helper 1',
        'last_name' => 'User',
        'email' => 'helper1@gmail.com',
        'password' => Hash::make('Helper12024'),
        'phone' => '00001',
        'address' => '-',
        'job' => 'Helper',
        'email_verified_at' => Carbon::now(),
        'demo_quota' => 1,
    ]);
    $user2 = User::create([
        'first_name' => 'Helper 2',
        'last_name' => 'User',
        'email' => 'helper2@gmail.com',
        'password' => Hash::make('Helper22024'),
        'phone' => '00002',
        'address' => '-',
        'job' => 'Helper',
        'email_verified_at' => Carbon::now(),
        'demo_quota' => 1,
    ]);

    $user1->assignRole('root');
    $user2->assignRole('root');

    return dd('OK');
});

Route::get('/auth/email-verification/confirm/{token}', [RegisterController::class, 'confirmEmail'])->name('register.confirm_email');

Route::get('/clear-order', function() {
    $projects = Project::all();

    foreach ($projects as $project) {
        if ($project->order()->count() > 1) {
            $project->order[1]->delete();
        }
    }

    return dd('OK');

});

Route::get('/populate-demo-projects', function() {

    DB::beginTransaction();

    $projects = Project::all();
    $datas = [];

    foreach ($projects as $project) {
            $datas[] = [
                    'order_id' => strtoupper(uniqid()),
                    'user_id' => $project->user_id,
                    'type' => 'demo',
                    'project_id' => $project->id,
                    'is_active' => true,
                    'expired_at' => Carbon::now()->subMonth(-1),
                    'subscription_id' => 'demo',
            ];
    }

    Order::insert($datas);

    DB::commit();
});
