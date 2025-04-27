<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Models\User;
class AuthController extends Controller
{
     // Register User (Role Default = 'user')
     public function signup(Request $request)
     {
         $request->validate([
             'username' => 'required|string|max:255',
             'email' => 'required|string|email|max:255|unique:users',
             'password' => 'required|string|min:8|confirmed',
         ]);
 
         $user = User::create([
             'username' => $request->username,
             'email' => $request->email,
             'password' => Hash::make($request->password),
             'role' => 'user', // Default role user
         ]);
 
         $token = $user->createToken('auth_token')->plainTextToken;
 
         return response()->json([
             'message' => 'User registered successfully',
             'access_token' => $token,
             'token_type' => 'Bearer',
             'user' => $user
         ], 201);
     }
 
     // Login User
     public function signin(Request $request)
     {
         $request->validate([
             'email' => 'required|email',
             'password' => 'required'
         ]);
 
         $user = User::where('email', $request->email)->first();
 
         if (!$user || !Hash::check($request->password, $user->password)) {
             return response()->json(['message' => 'Invalid credentials'], 401);
         }
 
         $token = $user->createToken('auth_token')->plainTextToken;
 
         return response()->json([
             'message' => 'Login successful',
             'access_token' => $token,
             'token_type' => 'Bearer',
             'role' => $user->role,
             'user' => $user
         ], 200);
     }
 
     // Logout User
     public function logout(Request $request)
     {
         $request->user()->tokens()->delete();
         
         return response()->json(['message' => 'Logged out successfully'], 200);
     }

     public function forgotPassword(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink($request->only('email'));

    return $status === Password::RESET_LINK_SENT
        ? response()->json(['message' => 'Reset password link sent'], 200)
        : response()->json(['message' => 'Failed to send reset link'], 400);
}

// Reset Password
public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'token' => 'required',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json(['message' => 'Password reset successful'], 200)
        : response()->json(['message' => 'Invalid token or email'], 400);
}
}