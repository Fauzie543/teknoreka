<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ProfileController extends Controller
{
      // Lihat Profil Pengguna
    public function show()
    {
        return response()->json([
            'user' => Auth::user()
        ], 200);
    }

    // Edit Profil (Username, Email, Foto Profil)
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'photo_profile' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'sometimes|required_with:new_password|string',
            'new_password' => 'sometimes|required_with:current_password|string|min:8|confirmed',
        ]);

        // Update data profil
        if ($request->has('username')) {
            $user->username = $request->username;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->hasFile('photo_profile')) {
            if ($user->photo_profile) {
                Storage::delete('public/profile_photos/' . $user->photo_profile);
            }
            $photoPath = $request->file('photo_profile')->store('public/profile_photos');
            $user->photo_profile = basename($photoPath);
        }

        // Ganti password jika diminta
        if ($request->filled('current_password') && $request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect.'
                ], 400);
            }

            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'photo_profile' => $user->photo_profile 
                    ? asset('storage/profile_photos/' . $user->photo_profile) 
                    : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ], 200);
    }
}