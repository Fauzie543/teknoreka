<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        ]);

        // Jika ada foto baru, hapus foto lama dan simpan yang baru
        if ($request->hasFile('photo_profile')) {
            if ($user->photo_profile) {
                Storage::delete('public/profile_photos/' . $user->photo_profile);
            }
            $photoPath = $request->file('photo_profile')->store('public/profile_photos');
            $user->photo_profile = basename($photoPath);
        }

        // Update username dan email
        $user->update($request->only('username', 'email', 'photo_profile'));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }
}