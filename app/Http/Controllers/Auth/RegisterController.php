<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Mail\EmailVerificationMail;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request) {
        $this->removeUnverifiedUser($request);
        try {
            $user = $this->createUserAccount($request);
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
        $user = User::where('email', Crypt::decryptString($token))->first();

        Log::info('[INFO] Confirming email at ' . Carbon::now()->format('Y'));
        Log::info('[INFO] Email token : ' . $token);

        if (!$user) {
            Log::error('[INFO] Email verification failed, no user assigned for token : ' . $token);
            return redirect(config('app.email_verification.callback_domain') . '/auth/verification/callback?status=fail&msg=invalid');
        }

        Log::info('[INFO] Email verified successfully for uid : ' . $user->hashid);

        $user->email_verified_at = Carbon::now();
        $user->save();

        return redirect(config('app.email_verification.callback_domain') . '/auth/verification/callback?status=success&uid=' . $user->hashid);
    }

    protected function sendVerificationMail(User $user)
    {
        $token = Crypt::encryptString($user->email);
        $user->verification_token = $token;
        $user->save();
        Mail::to($user->email)->send(new EmailVerificationMail($user, $token));
    }

    private function removeUnverifiedUser(RegisterRequest $request) {
      $existingUser = User::where('email', $request->email)
                          ->orWhere('phone', $request->phone)
                          ->first();
      if(!$existingUser) { return; }
      if ($existingUser->email_verified_at === null) {
        $existingUser->delete();
        return;
      }
      $validationRules = $request->rules();
      $validationRules['email'] .= "|unique:users,email";
      $validationRules['phone'] .= "|unique:users,phone";
      $request->validate($validationRules);
    }

    private function createUserAccount(RegisterRequest $request) {
        $request->merge([
            'password' => Hash::make($request->password),
            'demo_quota' => 1
        ]);
        $user = User::create($request->only(['first_name', 'last_name', 'email', 'password', 'job', 'phone', 'demo_quota']));
        $user->assignRole('owner');
        if (!app()->environment('local')) {
            $this->sendVerificationMail($user);
        }
        return $user;
    }
}
