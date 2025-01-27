<?php

namespace App\Http\Controllers;

use App\Mail\VerificationEmail;
use App\Models\VerificationCode;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TokenVerificationController extends Controller
{
    public function send_code(Request $request){
        $token = $request->user()->currentAccessToken();

        if($token->verified){
            return response()->json([
                'success' => false,
                'message' => 'Token already verified'
            ]);
        }

        $code = random_int(100000, 999999);

        // Delete existing codes
        VerificationCode::where('personal_access_token_id', $token->id)->delete();

        $code = VerificationCode::create([
            'personal_access_token_id' => $token->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(5)
        ]);

        // Send the code via email
        Mail::to($request->user())->send(new VerificationEmail($code));

        return response()->json([
            'success' => true,
            'message' => 'Verification code generated',
            'expires_at' => $code->expires_at
        ]);
    }

    public function verify_code(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if($token->verified){
            return response()->json([
                'success' => false,
                'message' => 'Token already verified'
            ]);
        }

        $request->validate([
            'code' => "required"
        ]);

        $code = VerificationCode::where('personal_access_token_id', $token->id)->first();

        if(!$code){
            return response()->json([
                'success' => false,
                'message' => 'Code not found'
            ]);
        }

        if($code->expires_at->isPast()){
            $code->delete();
            return response()->json([
                'success' => false,
                'message' => 'The code has expired'
            ]);
        }

        if($code->code == $request->code){
            $code->delete();

            $token->verified = true;
            $token->save();

            return response()->json([
                'success' => true,
                'message' => 'Token verified successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'The code is incorrect'
        ]);
    }
}
