<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        // Step 1: Validate user input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Step 2: Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Step 3: Return a JSON response
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }



public function login(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $credentials = [
        'email' => $validated['email'],
        'password' => $validated['password'],
    ];

    $token = auth('api')->attempt($credentials);

    if (!$token) {
        return response()->json([
            'message' => 'Invalid email or password',
        ], 401);
    }

    return response()->json([
        'message' => 'Login successful',
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60,
        'user' => auth('api')->user(),
    ]);
}



public function me()
{
    return response()->json([
        'user' => auth('api')->user(),
    ]);
}

public function logout()
{
    auth('api')->logout();

    return response()->json([
        'message' => 'Logged out successfully',
    ]);
}



}
