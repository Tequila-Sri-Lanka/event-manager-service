<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RefreshPersonalAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::check()){
            $token = $request->user()->currentAccessToken();
            $token->update([
                'expires_at' => now()->addHours(env('SESSION_TIMEOUT_HRS', 2))
            ]);
        }
        return $next($request);
    }
}
