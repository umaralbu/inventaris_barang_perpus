<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\StaffController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Inventaris Barang Perpustakaan V2
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Autentikasi Pengguna (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

// Area Terautentikasi (Auth Only)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ====================================================================
    // MODUL INVENTARIS: JELAJAH KATALOG & DETAIL (ADMIN & PEGAWAI)
    // ====================================================================
    Route::get('/inventory/export-csv', [InventoryController::class, 'exportCsv'])->name('inventory.export_csv');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

    // ====================================================================
    // MODUL TRANSAKSI (PEMINJAMAN, PENGEMBALIAN & RIWAYAT)
    // ====================================================================
    Route::get('/transactions/export-csv', [TransactionController::class, 'exportCsv'])->name('transactions.export_csv');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/borrow/create', [TransactionController::class, 'createBorrow'])->name('transactions.borrow.create');
    Route::post('/transactions/borrow', [TransactionController::class, 'storeBorrow'])->name('transactions.borrow.store');
    Route::get('/transactions/return/create', [TransactionController::class, 'createReturn'])->name('transactions.return.create');
    Route::post('/transactions/return', [TransactionController::class, 'storeReturn'])->name('transactions.return.store');

    // ====================================================================
    // MODUL MASTER DATA & MANAJEMEN INVENTARIS (KHUSUS ADMIN)
    // ====================================================================
    Route::middleware('role:admin')->group(function () {
        // Antrean Persetujuan Transaksi
        Route::get('/transactions/pending', [TransactionController::class, 'pending'])->name('transactions.pending');
        Route::get('/transactions/{transaction}/approve', [TransactionController::class, 'showApproval'])->name('transactions.approve.show');
        Route::post('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
        Route::post('/transactions/{transaction}/reject', [TransactionController::class, 'reject'])->name('transactions.reject');

        // Perpindahan Lokasi Massal (Batch Transfer)
        Route::get('/transactions/transfer/create', [TransactionController::class, 'createTransfer'])->name('transactions.transfer.create');
        Route::post('/transactions/transfer', [TransactionController::class, 'storeTransfer'])->name('transactions.transfer.store');

        // Pemeliharaan & Perbaikan Barang (Maintenance Lifecycle)
        Route::get('/transactions/maintenance', [TransactionController::class, 'maintenanceIndex'])->name('transactions.maintenance.index');
        Route::post('/transactions/maintenance/start', [TransactionController::class, 'startMaintenance'])->name('transactions.maintenance.start');
        Route::post('/transactions/maintenance/{transaction}/complete', [TransactionController::class, 'completeMaintenance'])->name('transactions.maintenance.complete');

        // Inventaris Admin Actions (Katalog)
        Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/inventory/{catalog}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::put('/inventory/{catalog}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{catalog}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::patch('/inventory/{catalog}/toggle-status', [InventoryController::class, 'toggleStatus'])->name('inventory.toggle');

        // Inventaris Admin Actions (Unit Fisik)
        Route::post('/inventory/{catalog}/units', [InventoryController::class, 'storeUnit'])->name('inventory.units.store');
        Route::put('/inventory/units/{unit}', [InventoryController::class, 'updateUnit'])->name('inventory.units.update');
        Route::delete('/inventory/units/{unit}', [InventoryController::class, 'destroyUnit'])->name('inventory.units.destroy');
        Route::patch('/inventory/units/{unit}/toggle-status', [InventoryController::class, 'toggleUnit'])->name('inventory.units.toggle');

        // Kategori Barang
        Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle');
        Route::resource('categories', CategoryController::class);

        // Lokasi Ruangan / Rak
        Route::patch('locations/{location}/toggle-status', [LocationController::class, 'toggleStatus'])->name('locations.toggle');
        Route::resource('locations', LocationController::class);

        // Staf Perpustakaan
        Route::patch('staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle');
        Route::resource('staff', StaffController::class);

        // Akun Pengguna (Users)
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle');
        Route::resource('users', UserController::class);

        // Rute pengujian role admin
        Route::get('/admin/test-access', function () {
            return response()->json(['status' => 'success', 'message' => 'Halo Admin, Anda memiliki hak akses penuh!']);
        })->name('admin.test');
    });

    // Detail Katalog (ditempatkan setelah /inventory/create agar tidak konflik wildcard)
    Route::get('/inventory/{catalog}', [InventoryController::class, 'show'])->name('inventory.show');

    // Detail & Pembatalan Transaksi
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

    // Rute pengujian role pegawai
    Route::middleware('role:pegawai')->prefix('pegawai')->name('pegawai.')->group(function () {
        Route::get('/test-access', function () {
            return response()->json(['status' => 'success', 'message' => 'Halo Pegawai, Anda memiliki hak akses area pegawai!']);
        })->name('test');
    });
});
