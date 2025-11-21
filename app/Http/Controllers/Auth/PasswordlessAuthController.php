<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class PasswordlessAuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle passwordless login - create or find user by name.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find or create user by name
        $user = User::firstOrCreate(
            ['name' => $request->name],
            ['last_login_at' => now()]
        );

        // Update last login
        $user->last_login_at = now();
        $user->save();

        // Generate auth token
        $token = $user->generateAuthToken();

        // Set cookie (30 days)
        $cookie = Cookie::make('auth_token', $token, 60 * 24 * 30, '/', null, false, true);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'token' => $token,
            ]
        ])->cookie($cookie);
    }

    /**
     * Verify token and return user data.
     */
    public function verify(Request $request)
    {
        $token = $request->input('token') ?? $request->cookie('auth_token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'No token provided'
            ], 401);
        }

        $user = User::findByAuthToken($token);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'last_login_at' => $user->last_login_at,
            ]
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        $token = $request->input('token') ?? $request->cookie('auth_token');

        if ($token) {
            $user = User::findByAuthToken($token);
            if ($user) {
                $user->auth_token = null;
                $user->save();
            }
        }

        $cookie = Cookie::forget('auth_token');

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ])->cookie($cookie);
    }
}
