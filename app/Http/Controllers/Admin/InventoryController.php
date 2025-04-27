<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Inventory;
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
        return response()->json([
            'message' => 'Inventories fetched successfully',
            'data' => $query->get()
        ]);
    }

    public function show($id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }

        return response()->json([
            'message' => 'Inventory details fetched successfully',
            'data' => $inventory
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'placement' => 'required|string|max:255',
            'asset_entry' => 'nullable|date',
            'expired_date' => 'nullable|date',
            'is_can_loan' => 'required|boolean',
            'inv_status' => 'required|in:owned,loan',
            'quantity' => 'required|integer|min:1',
            'img_url' => 'nullable|string',
        ]);

        $inventory = Inventory::create($request->all());

        return response()->json([
            'message' => 'Inventory added successfully',
            'data' => $inventory
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:255',
            'placement' => 'sometimes|string|max:255',
            'asset_entry' => 'nullable|date',
            'expired_date' => 'nullable|date',
            'is_can_loan' => 'sometimes|boolean',
            'inv_status' => 'sometimes|in:owned,loan',
            'quantity' => 'sometimes|integer|min:1',
            'img_url' => 'nullable|string',
        ]);

        $inventory->update($request->all());

        return response()->json([
            'message' => 'Inventory updated successfully',
            'data' => $inventory
        ]);
    }

    public function destroy($id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }

        $inventory->delete();

        return response()->json([
            'message' => 'Inventory deleted successfully'
        ]);
    }

    public function filterLoans(Request $request)
{
    $status = $request->query('status', 'all');
    $id = $request->query('id');

    // Log semua parameter yang diterima
    Log::info('Query Parameters:', $request->query());

    // Validasi id harus angka jika ada
    $validator = Validator::make($request->all(), [
        'id' => 'nullable|integer'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'ID tidak valid',
            'errors' => $validator->errors()
        ], 400);
    }

    // Pastikan status hanya mengambil nilai yang diperbolehkan
    $allowedStatuses = ['all', 'company', 'loanable'];
    if (!in_array($status, $allowedStatuses)) {
        return response()->json([
            'message' => 'Filter status tidak valid',
            'data' => []
        ], 400);
    }

    // Mapping status ke database
    $statusMapping = [
        'company' => 'owned',
        'loanable' => 'loan'
    ];

    $query = Inventory::query();

    if ($status !== 'all') {
        if (isset($statusMapping[$status])) {
            $query->where('inv_status', $statusMapping[$status]);
        }
    }

    // Jika `id` tersedia dan valid, filter berdasarkan ID juga
    if ($id) {
        $query->where('id', $id);
    }

    // Debugging Query sebelum dieksekusi
    Log::info('Generated Query:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

    $assets = $query->get();

    return response()->json([
        'message' => 'Daftar aset berhasil difilter',
        'data' => $assets
    ]);
}
}