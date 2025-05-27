<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;
use App\Models\InventoryLoan;



class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter');

    $query = Inventory::query();

    if ($filter === 'owned') {
        $query->where('inv_status', 'owned');
    } elseif ($filter === 'loan') {
        $query->where('inv_status', 'loan');
    }

    $inventories = $query->get();

    $data = $inventories->map(function ($item) {
        $loaned = InventoryLoan::where('inventory_id', $item->id)
            ->whereNull('expired_at')
            ->sum('quantity');

        $item->available_quantity = $item->quantity - $loaned;

        return $item;
    });

    return response()->json([
        'message' => 'Inventories fetched successfully',
        'data' => $data
    ]);
    }

    public function show($id)
    {
        $inventory = Inventory::find($id);

    if (!$inventory) {
        return response()->json(['message' => 'Inventory not found'], 404);
    }

    $loaned = InventoryLoan::where('inventory_id', $inventory->id)
        ->whereNull('expired_at')
        ->sum('quantity');

    $inventory->available_quantity = $inventory->quantity - $loaned;

    return response()->json([
        'message' => 'Inventory details fetched successfully',
        'data' => $inventory
    ]);
    }

    // 🔹 2. Lihat status peminjaman user
    public function loanStatus()
    {
        $user = Auth::user(); // Ambil user yang sedang login

        $loans = InventoryLoan::with('inventory')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'message' => 'Status peminjaman berhasil diambil',
            'data' => $loans
        ], 200);
    }

    public function filterLoans(Request $request)
{
    $user = Auth::user();
    $filter = $request->query('filter', 'all');

    $query = InventoryLoan::with('inventory')->where('user_id', $user->id);

    if ($filter === 'company') {
        $query->whereHas('inventory', function ($q) {
            $q->where('inv_status', 'owned'); // Aset milik perusahaan
        });
    } elseif ($filter === 'loanable') {
        $query->whereHas('inventory', function ($q) {
            $q->where('inv_status', 'loan'); // Aset yang dipinjam
        });
    }

    $loans = $query->get();

    return response()->json([
        'message' => 'Daftar peminjaman berhasil difilter',
        'data' => $loans
    ]);
}

}