<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryLoan;
use App\Models\Inventory;

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
            'inventory_id' => 'required|exists:inventorys,id',
            'user_id' => 'required|exists:users,id',
            'start_at' => 'required|date',
            'expired_at' => 'required|date|after:start_at',
            'quantity' => 'required|integer|min:1'
        ]);

        $inventory = Inventory::findOrFail($request->inventory_id);

        if (!$inventory->is_can_loan || $request->quantity > $inventory->available_quantity) {
            return response()->json(['message' => 'Stok tidak mencukupi untuk peminjaman'], 422);
        }

        $loan = InventoryLoan::create($request->all());

        $inventory->refresh();
        if ($inventory->available_quantity <= 0 && $inventory->is_can_loan) {
            $inventory->update(['is_can_loan' => false]);
        }

        return response()->json([
            'message' => 'Peminjaman aset berhasil ditambahkan',
            'data' => $loan
        ], 201);
}


public function update(Request $request, $id)
{
     $loan = InventoryLoan::findOrFail($id);
        $inventory = $loan->inventory;

        $request->validate([
            'inventory_id' => 'sometimes|exists:inventorys,id',
            'user_id' => 'sometimes|exists:users,id',
            'start_at' => 'sometimes|date',
            'expired_at' => 'sometimes|date|after:start_at',
            'quantity' => 'sometimes|integer|min:1'
        ]);

        if ($request->has('quantity')) {
            $newQty = $request->quantity;
            $oldQty = $loan->quantity;
            $diff = $newQty - $oldQty;

            if ($diff > 0 && $diff > $inventory->available_quantity) {
                return response()->json(['message' => 'Stok tidak mencukupi untuk update peminjaman'], 422);
            }
        }

        $loan->update($request->all());

        $inventory->refresh();
        $inventory->update(['is_can_loan' => $inventory->available_quantity > 0]);

        return response()->json([
            'message' => 'Peminjaman aset berhasil diperbarui',
            'data' => $loan
        ]);
}

public function destroy($id)
{
     $loan = InventoryLoan::findOrFail($id);
    $inventory = $loan->inventory;
    
    $loan->delete();

    // Perbarui status pinjam jika stok tersedia kembali
    if ($inventory->available_quantity > 0) {
        $inventory->update(['is_can_loan' => true]);
    }

    return response()->json([
        'message' => 'Peminjaman aset berhasil dihapus'
    ]);
}

}