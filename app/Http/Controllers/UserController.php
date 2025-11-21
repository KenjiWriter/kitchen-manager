<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Search users by name.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query || strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'users' => []
            ]);
        }

        $users = User::where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'last_login_at')
            ->limit(20)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'last_login' => $user->last_login_at?->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
}
