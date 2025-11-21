<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get token from request or cookie
        $token = $request->input('token') 
                 ?? $request->header('X-Auth-Token') 
                 ?? $request->cookie('auth_token');

        if (!$token) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - No token provided'
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = User::findByAuthToken($token);

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Invalid token'
                ], 401);
            }
            return redirect()->route('login');
        }

        // Attach user to request
        $request->merge(['auth_user' => $user]);
        $request->attributes->set('auth_user', $user);

        return $next($request);
    }
}
