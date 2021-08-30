<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordMail;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function sendConfirmationMail(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {

            $token = PasswordReset::create([
                'email' => $request->email,
                'token' => Str::random(32),
            ]);

            Mail::to($request->email)->send(new ForgotPasswordMail($user, $token));
        }
    }
}
