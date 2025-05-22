<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Buat admin baru
        $admin = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin', // Pastikan ada kolom role di tabel users
        ]);

        return response()->json([
            'message' => 'Admin baru berhasil ditambahkan',
            'admin' => $admin
        ], 201);
    }

    // Lihat semua user
    public function index(Request $request)
    {
        try {
            $query = User::where('role', 'user');

            if ($request->filled('search')) {
                $query->where('username', 'like', '%' . $request->search . '%');
            }

            $users = $query->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'photo_profile' => $user->photo_profile ?? null,
                    'created_at' => $user->created_at,
                ];
            });

            return response()->json([
                'message' => 'Daftar user berhasil diambil',
                'data' => $users
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal ambil user',
                'error' => $e->getMessage()
            ], 500);
        }
    }




// Lihat detail user berdasarkan ID
public function show($id)
{
    $user = User::where('role', 'user')->findOrFail($id);

    return response()->json([
        'user' => [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'created_at' => $user->created_at,
        ]
    ]);
}

// Update password user
public function updateUser(Request $request, $id)
{
    $user = User::where('role', 'user')->findOrFail($id);

    // Validasi dinamis
    $rules = [];

    if ($request->has('password')) {
        $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
    }

    if ($request->has('role')) {
        $rules['role'] = ['required', 'in:admin,user'];
    }

    $request->validate($rules);

    // Update password jika ada
    if ($request->has('password')) {
        $user->password = Hash::make($request->password);
    }

    // Update role jika ada
    if ($request->has('role')) {
        $user->role = $request->role;
    }

    $user->save();

    return response()->json([
        'message' => 'Data user berhasil diperbarui',
        'user' => [
            'id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
        ]
    ]);
}



}