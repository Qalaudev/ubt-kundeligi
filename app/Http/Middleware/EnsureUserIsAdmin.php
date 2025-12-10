<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For API routes, use Sanctum token authentication
        if ($request->is('api/*')) {
            if (!auth('sanctum')->check()) {
                return response()->json([
                    'message' => 'Unauthenticated. Please login to access this resource.'
                ], 401);
            }

            if (!auth('sanctum')->user()->isAdmin()) {
                return response()->json([
                    'message' => 'Unauthorized. You do not have permission to access this resource.'
                ], 403);
            }
        } else {
            // For web routes, use session-based auth
            if (!auth('web')->check()) {
                return redirect()->route('login')->with('error', 'Please login to access this page.');
            }

            if (!auth('web')->user()->isAdmin()) {
                return redirect()->route('home')->with('error', 'You do not have permission to access this page.');
            }
        }

        return $next($request);
    }
}
