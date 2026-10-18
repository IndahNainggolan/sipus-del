<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class BukuSampelSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriId = Kategori::query()->value('id');

        if ($kategoriId === null) {
            $this->command?->error(
                'Kategori belum tersedia. Jalankan KategoriSeeder terlebih dahulu.'
            );

            return;
        }

        Buku::updateOrCreate(
            ['isbn' => '9786020000001'],
            [
                'judul' => 'Buku Sampel Stok Habis',
                'penulis' => 'Penulis Sampel',
                'penerbit' => 'Penerbit Sampel',
                'tahun_terbit' => 2024,
                'kategori_id' => $kategoriId,
                'stok' => 0,
                'sinopsis' => 'Buku untuk menguji penolakan peminjaman ketika stok habis.',
            ]
        );

        Buku::updateOrCreate(
            ['isbn' => '9786020000002'],
            [
                'judul' => 'Buku Sampel Stok Satu',
                'penulis' => 'Penulis Sampel',
                'penerbit' => 'Penerbit Sampel',
                'tahun_terbit' => 2025,
                'kategori_id' => $kategoriId,
                'stok' => 1,
                'sinopsis' => 'Buku untuk menguji peminjaman ketika hanya satu eksemplar tersedia.',
            ]
        );

        $this->command?->info('Buku sampel stok 0 dan stok 1 berhasil disiapkan.');
    }
}
