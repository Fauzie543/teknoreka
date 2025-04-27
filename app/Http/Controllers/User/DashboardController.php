<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\InventoryLoan;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil data aset dari database
        $totalAset = Inventory::count();
        $totalTersedia = Inventory::where('inv_status', 'owned')->count();
        $totalDipinjam = Inventory::where('inv_status', 'loan')->count();
        $totalPeminjaman = InventoryLoan::count();

        // Data untuk diagram
        $dataDiagram = [
            'total_aset' => $totalAset,
            'aset_tersedia' => $totalTersedia,
            'aset_dipinjam' => $totalDipinjam,
            'total_peminjaman' => $totalPeminjaman
        ];

        return response()->json([
            'message' => 'Dashboard data fetched successfully',
            'data' => $dataDiagram
        ]);
    }
}