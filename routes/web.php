<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\StaffController;
use App\Http\Controllers\Master\UserController;
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
    // MODUL MASTER DATA (KHUSUS ADMIN)
    // ====================================================================
    Route::middleware('role:admin')->group(function () {
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

    // Rute pengujian role pegawai
    Route::middleware('role:pegawai')->prefix('pegawai')->name('pegawai.')->group(function () {
        Route::get('/test-access', function () {
            return response()->json(['status' => 'success', 'message' => 'Halo Pegawai, Anda memiliki hak akses area pegawai!']);
        })->name('test');
    });
});
