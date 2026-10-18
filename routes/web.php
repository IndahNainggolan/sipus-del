
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;

Route::get('/', function () {
    return redirect()->route('bukus.index');
});

// Autentikasi Pustakawan
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Rute Terproteksi Pustakawan (Mutasi Data Buku, Kategori, Peminjaman, & Logout)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Mutasi Buku
    Route::get('bukus/create', [BukuController::class, 'create'])->name('bukus.create');
    Route::post('bukus', [BukuController::class, 'store'])->name('bukus.store');
    Route::get('bukus/{buku}/edit', [BukuController::class, 'edit'])->name('bukus.edit');
    Route::match(['put', 'patch'], 'bukus/{buku}', [BukuController::class, 'update'])->name('bukus.update');
    Route::delete('bukus/{buku}', [BukuController::class, 'destroy'])->name('bukus.destroy');

    // Mutasi Kategori
    Route::get('kategoris/create', [KategoriController::class, 'create'])->name('kategoris.create');
    Route::post('kategoris', [KategoriController::class, 'store'])->name('kategoris.store');
    Route::get('kategoris/{kategori}/edit', [KategoriController::class, 'edit'])->name('kategoris.edit');
    Route::match(['put', 'patch'], 'kategoris/{kategori}', [KategoriController::class, 'update'])->name('kategoris.update');
    Route::delete('kategoris/{kategori}', [KategoriController::class, 'destroy'])->name('kategoris.destroy');

    // Mutasi Peminjaman & Pengembalian
    Route::get('peminjamans/create', [PeminjamanController::class, 'create'])->name('peminjamans.create');
    Route::post('peminjamans', [PeminjamanController::class, 'store'])->name('peminjamans.store');
    Route::post('peminjamans/{peminjaman}/kembalikan', [PeminjamanController::class, 'kembalikan'])->name('peminjamans.kembalikan');
});

// Rute Publik (Read-Only)
Route::get('bukus', [BukuController::class, 'index'])->name('bukus.index');
Route::get('bukus/{buku}', [BukuController::class, 'show'])->name('bukus.show');
Route::get('kategoris', [KategoriController::class, 'index'])->name('kategoris.index');
Route::get('kategoris/{kategori}', [KategoriController::class, 'show'])->name('kategoris.show');
Route::get('peminjamans', [PeminjamanController::class, 'index'])->name('peminjamans.index');
