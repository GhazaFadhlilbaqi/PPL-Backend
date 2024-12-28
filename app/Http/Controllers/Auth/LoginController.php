<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationMail;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'The credentials is wrong'
            ], 401);
        }
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Check Email Verification Status
        if (is_null($user ->email_verified_at)) {
            $this->sendVerificationMail(Auth::user());
            Auth::logout();
            return response()->json([
                'status' => 'fail',
                'message' => 'Email anda belum diverifikasi. Instruksi verifikasi telah dikirim ke alamat email Anda. Silakan cek dan ikuti petunjuknya untuk menyelesaikan verifikasi.'
            ], 401);
        }

        $user->update(['last_login' => now()]);
        $user->getAllPermissions();
        $token = $user->createToken('auth');

        // Populate company data
        $user->company;

        return response()->json([
            'status' => 'success',
            'data' => compact('user', 'token')
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'logged out'
        ]);
    }

    public function verify(Request $request)
    {
        // NOTE: Token format must be : Bearer {token}
        if (Auth::check()) {
            $request->user()->getAllPermissions();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $request->user(),
                    'token' => explode(' ', $request->header('Authorization'))[1],
                    'tokenAbilities' => $request->user()->currentAccessToken()->abilities,
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'fail',
                'message' => 'Unauthenticated User',
            ], 401);
        }
    }

    protected function sendVerificationMail(User $user)
    {
        // FIXME: 🤯 Make verification token fix (no changes after re-sending the verification mail)
        // $token = Crypt::encryptString($user->email);

        // $user->verification_token = $token;
        // $user->save();

        Mail::to($user->email)->send(new EmailVerificationMail($user, $user->verification_token));
    }
}
