<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Profile Shown',
            'session_verified' => $user->currentAccessToken()->verified,
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'in:admin,manager,organizer',
            'current_password' => 'required|string|current_password',
            'password' => 'min:8|confirmed',
        ]);

        if($user->role != 'admin'){
            unset($validated['role']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile Updated',
            'session_verified' => $user->currentAccessToken()->verified,
            'user' => $user
        ]);
    }
}
