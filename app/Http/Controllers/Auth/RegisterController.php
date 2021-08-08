<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Mail\EmailVerificationMail;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            $request->merge([
                'password' => Hash::make($request->password)
            ]);

            $user = User::create($request->only(['first_name', 'last_name', 'email', 'password', 'job', 'phone']));

            // Send verification mail
            if ($user) $this->sendVerificationMail($user);

            return response()->json([
                'status' => 'success',
                'data' => compact('user')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'traces' => $e->getTrace(),
            ]);
        }
    }

    public function confirmEmail($token)
    {
        $user = User::where('verification_token', $token)->where('email_verified_at', null)->first();

        if (!$user) return redirect(config('app.email_verification.callback_domain') . '/auth/verification/callback?status=fail&msg=invalid');

        $user->verification_token = null;
        $user->email_verified_at = Carbon::now();
        $user->save();

        return redirect(config('app.email_verification.callback_domain') . '/auth/verification/callback?status=success&uid=' . $user->hashid);
    }

    protected function sendVerificationMail(User $user)
    {
        $token = Str::random(32);

        $user->verification_token = $token;
        $user->save();

        Mail::to($user->email)->send(new EmailVerificationMail($user, $token));
    }
}
