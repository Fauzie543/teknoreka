<?php

namespace App\Http\Controllers\Admin;

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
    
    public function store(Request $request)
{
    $request->validate([
        'inventory_id' => 'required|exists:inventories,id',
        'user_id' => 'required|exists:users,id',
        'start_at' => 'required|date',
        'expired_at' => 'required|date|after:start_at',
        'quantity' => 'required|integer|min:1'
    ]);

    $loan = InventoryLoan::create($request->all());

    return response()->json([
        'message' => 'Peminjaman aset berhasil ditambahkan',
        'data' => $loan
    ], 201);
}

public function update(Request $request, $id)
{
    $loan = InventoryLoan::findOrFail($id);

    $request->validate([
        'inventory_id' => 'sometimes|exists:inventorys,id',
        'user_id' => 'sometimes|exists:users,id',
        'start_at' => 'sometimes|date',
        'expired_at' => 'sometimes|date|after:start_at',
        'quantity' => 'sometimes|integer|min:1'
    ]);

    $loan->update($request->all());

    return response()->json([
        'message' => 'Peminjaman aset berhasil diperbarui',
        'data' => $loan
    ]);
}

public function destroy($id)
{
    $loan = InventoryLoan::findOrFail($id);
    $loan->delete();

    return response()->json([
        'message' => 'Peminjaman aset berhasil dihapus'
    ]);
}

}