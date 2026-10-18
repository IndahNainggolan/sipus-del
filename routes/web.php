
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;

Route::get('/', function () {
    return redirect()->route('bukus.index');
});

Route::resource('bukus', BukuController::class);
Route::resource('kategoris', KategoriController::class);
Route::resource('peminjamans', PeminjamanController::class)->only(['index', 'create', 'store']);
Route::post('peminjamans/{peminjaman}/kembalikan', [PeminjamanController::class, 'kembalikan'])->name('peminjamans.kembalikan');
