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

            $user = Auth::user();
            $token = $user->createToken('auth');

            return response()->json([
                'status' => 'success',
                'data' => compact('user', 'token')
            ]);

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
        Auth::logout();

        return response()->json([
            'status' => 'success',
            'message' => 'logged out'
        ]);
    }
}
