<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        // Try to login
        if(Auth::attempt($credentials, $request->has('remember'))){
            $user = Auth::user();

            if($user->verified){                
                return response()->json([
                    'success' => true,
                    'message' => 'Login Successful',
                    'user' => $user,
                    'token' => $user->createToken($request->header('User-Agent'), ["*"], now()->addHours(env('SESSION_TIMEOUT_', 2)))->plainTextToken
                ]);
            } else{
                // Unverified
                return response()->json([
                    'success' => 'false',
                    'message' => 'Account is not verified'
                ], 401);
            }
        }

        // Login failed
        return response()->json([
            'success' => 'false',
            'message' => 'Invalid Credentials'
        ], 401);
    }
}
