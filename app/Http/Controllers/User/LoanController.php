<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryLoan;

class LoanController extends Controller
{
    public function index()
    {
        $loans = InventoryLoan::with('inventory', 'user')->get();

        return response()->json([
            'message' => 'Daftar peminjaman aset berhasil diambil',
            'data' => $loans
        ], 200);
    }

    // 🔹 4. Lihat detail peminjaman aset berdasarkan ID
    public function show($id)
    {
        $loan = InventoryLoan::with('inventory', 'user')->find($id);

        if (!$loan) {
            return response()->json(['message' => 'Peminjaman tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail peminjaman aset berhasil diambil',
            'data' => $loan
        ], 200);
    }
}