<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (Auth::attempt($credentials)) {

            if (!is_null(Auth::user()->email_verified_at)) {

                $user = Auth::user();
                $user->getAllPermissions();
                $token = $user->createToken('auth');

                return response()->json([
                    'status' => 'success',
                    'data' => compact('user', 'token')
                ]);

            } else {

                Auth::logout();

                return response()->json([
                    'status' => 'fail',
                    'message' => 'Please verify your email first'
                ], 401);
            }

        } else {

            return response()->json([
                'status' => 'fail',
                'message' => 'The credentials is wrong'
            ], 401);

        }
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
}
