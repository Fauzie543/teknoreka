<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\User\DashboardController as UserDashboard;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

    

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/signin', [AuthController::class, 'signin'])->name('signin');
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink($request->only('email'));

    return $status === Password::RESET_LINK_SENT
        ? response()->json(['message' => 'Reset link sent'])
        : response()->json(['message' => 'Failed to send reset link'], 400);
})->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'show']); // Lihat Profil
    Route::post('/profile/update', [ProfileController::class, 'update']); // Edit Profil
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::post('/register', [App\Http\Controllers\Admin\AdminController::class, 'store']);
    Route::get('/dashboard', [AdminDashboard::class, 'index']);
    Route::get('/assets', [App\Http\Controllers\Admin\InventoryController::class, 'index']);
    Route::get('/assets/{id}', [App\Http\Controllers\Admin\InventoryController::class, 'show']);
    Route::post('/assets', [App\Http\Controllers\Admin\InventoryController::class, 'store']); // Tambah Aset
    Route::put('/assets/{id}', [App\Http\Controllers\Admin\InventoryController::class, 'update']); // Edit Aset
    Route::delete('/assets/{id}', [App\Http\Controllers\Admin\InventoryController::class, 'destroy']);
    



    Route::get('/loans', [App\Http\Controllers\Admin\LoanController::class, 'index']); // Lihat semua peminjaman
    Route::get('/loans/{id}', [App\Http\Controllers\Admin\LoanController::class, 'show']); // Lihat detail peminjaman
    Route::post('/loans', [App\Http\Controllers\Admin\LoanController::class, 'store']); // Tambah peminjaman
    Route::put('/loans/{id}', [App\Http\Controllers\Admin\LoanController::class, 'update']); // Edit peminjaman
    Route::delete('/loans/{id}', [App\Http\Controllers\Admin\LoanController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:user'])->prefix('user')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\User\DashboardController::class, 'index']);
    Route::get('/assets', [App\Http\Controllers\User\InventoryController::class, 'index']);
    // Manajemen Aset
    // Route::get('/assets', [App\Http\Controllers\User\InventoryController::class, 'index']);
    // Route::get('/assets/{id}', [App\Http\Controllers\User\InventoryController::class, 'show']);

    // // Manajemen Peminjaman Aset
    // Route::get('/loans', [App\Http\Controllers\User\LoanController::class, 'index']);
    // Route::get('/loans/{id}', [App\Http\Controllers\User\LoanController::class, 'show']);

    Route::get('/assets', [App\Http\Controllers\User\InventoryController::class, 'availableAssets']);
    Route::get('/loans', [App\Http\Controllers\User\InventoryController::class, 'loanStatus']);
    Route::get('/user/loans/filter', [App\Http\Controllers\User\InventoryController::class, 'filterLoans']);

});