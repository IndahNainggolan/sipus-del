<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\BukuApiController;
use App\Http\Controllers\Api\V1\PeminjamanApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/auth/login', [AuthTokenController::class, 'login'])->name('auth.login');

    Route::get('/bukus', [BukuApiController::class, 'index'])->name('bukus.index');
    Route::get('/bukus/{buku}/referensi-eksternal', [BukuApiController::class, 'externalReference'])
        ->name('bukus.referensi-eksternal');
    Route::get('/bukus/{buku}', [BukuApiController::class, 'show'])->name('bukus.show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthTokenController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AuthTokenController::class, 'logout'])->name('auth.logout');

        Route::post('/bukus', [BukuApiController::class, 'store'])->name('bukus.store');
        Route::put('/bukus/{buku}', [BukuApiController::class, 'replace'])->name('bukus.replace');
        Route::patch('/bukus/{buku}', [BukuApiController::class, 'update'])->name('bukus.update');
        Route::delete('/bukus/{buku}', [BukuApiController::class, 'destroy'])->name('bukus.destroy');

        Route::get('/peminjamans', [PeminjamanApiController::class, 'index'])->name('peminjamans.index');
        Route::post('/peminjamans', [PeminjamanApiController::class, 'store'])->name('peminjamans.store');
        Route::get('/peminjamans/{peminjaman}', [PeminjamanApiController::class, 'show'])->name('peminjamans.show');
        Route::patch('/peminjamans/{peminjaman}/kembalikan', [PeminjamanApiController::class, 'kembalikan'])
            ->name('peminjamans.kembalikan');
    });
});
