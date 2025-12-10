<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBannedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for API token authentication
        if ($request->is('api/*') && auth('sanctum')->check()) {
            if (auth('sanctum')->user()->isBanned()) {
                // Revoke all tokens for banned user
                auth('sanctum')->user()->tokens()->delete();
                return response()->json([
                    'message' => 'Your account has been banned. Please contact the administrator.'
                ], 403);
            }
        }
        
        // Check for web session authentication
        if (auth('web')->check() && auth('web')->user()->isBanned()) {
            auth('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')->with('error', 'Your account has been banned. Please contact the administrator.');
        }

        return $next($request);
    }
}
