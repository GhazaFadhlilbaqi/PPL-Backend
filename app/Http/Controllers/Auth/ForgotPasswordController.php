<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyTokenRequest;
use App\Mail\ForgotPasswordMail;
use App\Models\PasswordReset;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function sendConfirmationMail(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if ($user) {

                $passwordResetToken = PasswordReset::where('email', $request->email);
                if ($passwordResetToken->count() > 0) $passwordResetToken->delete();

                $token = PasswordReset::create([
                    'email' => $request->email,
                    'token' => Str::random(32),
                ]);

                Mail::to($request->email)->send(new ForgotPasswordMail($user, $token));
            }

            return response()->json([
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyResetToken(VerifyTokenRequest $request)
    {
        $passwordReset = PasswordReset::where('token', $request->reset_password_token)->first();
        if ($passwordReset) {
            return response()->json([
                'status' => 'success',
            ]);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid token'
            ], 401);
        }
    }

    public function resetPassword(ResetPasswordRequest $request, $token)
    {

        $passwordReset = PasswordReset::where('token', $token)->first();
        $user = User::where('email', $passwordReset->email)->first();

        $user->password = Hash::make($request->password);
        $user->save();

        $passwordReset->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Password resetted',
        ]);
    }
}
