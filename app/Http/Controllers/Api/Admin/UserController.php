<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(): JsonResponse
    {
        try {
            $users = User::select('id', 'name', 'email', 'role', 'banned_at', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role ?? 'user',
                        'banned_at' => $user->banned_at ? $user->banned_at->format('Y-m-d H:i:s') : null,
                        'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
                    ];
                });
            return response()->json($users->values());
        } catch (\Exception $e) {
            \Log::error('Error loading users: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error loading users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ban a user.
     */
    public function ban(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Prevent banning admins
        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'Cannot ban admin users.'
            ], 403);
        }

        $user->update(['banned_at' => now()]);

        return response()->json([
            'message' => 'User has been banned successfully.',
            'user' => $user
        ]);
    }

    /**
     * Unban a user.
     */
    public function unban(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['banned_at' => null]);

        return response()->json([
            'message' => 'User has been unbanned successfully.',
            'user' => $user
        ]);
    }
}
