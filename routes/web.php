<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
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

    // Contoh rute terproteksi role untuk testing middleware
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/test-access', function () {
            return response()->json(['status' => 'success', 'message' => 'Halo Admin, Anda memiliki hak akses penuh!']);
        })->name('test');
    });

    Route::middleware('role:pegawai')->prefix('pegawai')->name('pegawai.')->group(function () {
        Route::get('/test-access', function () {
            return response()->json(['status' => 'success', 'message' => 'Halo Pegawai, Anda memiliki hak akses area pegawai!']);
        })->name('test');
    });
});
