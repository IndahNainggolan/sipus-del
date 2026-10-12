<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            [
                'kode_kategori' => 'KTG001',
                'nama_kategori' => 'Teknologi Informasi',
            ],
            [
                'kode_kategori' => 'KTG002',
                'nama_kategori' => 'Pemrograman',
            ],
            [
                'kode_kategori' => 'KTG003',
                'nama_kategori' => 'Basis Data',
            ],
            [
                'kode_kategori' => 'KTG004',
                'nama_kategori' => 'Jaringan Komputer',
            ],
            [
                'kode_kategori' => 'KTG005',
                'nama_kategori' => 'Sistem Informasi',
            ],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::updateOrCreate(
                ['kode_kategori' => $kategori['kode_kategori']],
                ['nama_kategori' => $kategori['nama_kategori']]
            );
        }
    }
}
