
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;

Route::get('/', function () {
    return redirect()->route('bukus.index');
});

Route::resource('bukus', BukuController::class);
Route::resource('kategoris', KategoriController::class);
