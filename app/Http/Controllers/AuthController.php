<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->with('account') // Fetch account details automatically
            ->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            \Log::info('User found:', ['user' => $user]);

            return response()->json([
                'token' => $user->createToken('YourAppName')->plainTextToken,
                'id'    => $user->id,
                'name'  => optional($user->account)->name,
                'role'  => optional($user->account)->role,
                'account' => $user->account, // Ensure it returns full account details
            ], 200);

        return response()->json(['error' => 'Unauthorized'], 401);
    }
};
