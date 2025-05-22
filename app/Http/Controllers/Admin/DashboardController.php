<?php

namespace App\Http\Controllers\Admin;

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
        $totalTersedia = Inventory::where('is_can_loan', 'false')->count();
        $totalDipinjam = InventoryLoan::count();
        $totalPeminjaman = Inventory::where('inv_status', 'owned')->count();
        

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