<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\InventoryLoan;



class InventoryController extends Controller
{
    public function availableAssets()
    {
        $assets = Inventory::with('loans')
            ->where('is_can_loan', true)
            ->get()
            ->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'name' => $asset->name,
                    'category' => $asset->category,
                    'placement' => $asset->placement,
                    'quantity' => $asset->available_quantity, // Jumlah yang masih bisa dipinjam
                    'img_url' => $asset->img_url,
                ];
            });

        return response()->json([
            'message' => 'Daftar aset tersedia berhasil diambil',
            'data' => $assets
        ], 200);
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