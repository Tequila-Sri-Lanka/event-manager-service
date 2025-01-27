<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetEmail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    public function forgot_password(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => "This email is not registered"
        ]);

        $user = User::where('email', $request->email)->firstOrFail();
        $code = random_int(100000, 999999);

        $passwordResetCode = $user->password_reset_code()->updateOrCreate([],[
            'code' => $code,
            'expires_at' => now()->addMinutes(60)
        ]);

        Mail::to($user)->send(new PasswordResetEmail($passwordResetCode));

        return response()->json([
            'success' => true,
            'message' => 'Password reset code sent'
        ]);
    }

    public function reset_password(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'email.exists' => "This email is not registered"
        ]);

        $user = User::where('email', $request->email)->firstOrFail();
        $passwordResetCode = $user->password_reset_code;

        if($passwordResetCode && !$passwordResetCode->expires_at->isPast() && $passwordResetCode->code == $request->code){
            $passwordResetCode->delete();
            $user->forceFill(['password' => $request->password]);
            return response()->json([
                'success' => true,
                'message' => 'Password reset successful'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid code'
            ]);
        }
    }
}
