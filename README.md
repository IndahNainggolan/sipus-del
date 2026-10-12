# SIPUS-Del — Sistem Informasi Perpustakaan

SIPUS-Del adalah aplikasi web sederhana untuk mengelola data buku dan kategori buku pada perpustakaan. Aplikasi ini dibuat menggunakan Laravel sebagai bagian dari praktikum Pemrograman dan Pengembangan Web.

## Fitur

- Menampilkan daftar buku.
- Menambahkan data buku baru.
- Melihat detail buku.
- Mengedit data buku.
- Menghapus data buku.
- Mengelola kategori buku melalui fitur CRUD.
- Menghubungkan buku dengan kategori menggunakan Eloquent Relationship.
- Melakukan validasi input.
- Menampilkan data buku beserta kategorinya menggunakan eager loading.
- Menampilkan daftar buku dengan pagination.
- Menampilkan pesan berhasil setelah operasi tertentu.

## Teknologi yang Digunakan

- PHP 8.4
- Laravel 13
- SQLite
- Blade Template
- Eloquent ORM
- HTML dan CSS

## Struktur Data

### Tabel `kategoris`

- `id`
- `kode_kategori`
- `nama_kategori`
- `created_at`
- `updated_at`

### Tabel `bukus`

- `id`
- `isbn`
- `judul`
- `penulis`
- `penerbit`
- `tahun_terbit`
- `kategori_id`
- `stok`
- `sinopsis`
- `created_at`
- `updated_at`

## Relasi Database

- Satu kategori dapat memiliki banyak buku (*One-to-Many*).
- Satu buku hanya terhubung dengan satu kategori (*Many-to-One*).

Relasi dibuat menggunakan Eloquent Relationship pada model `Kategori` dan `Buku`.

## Eager Loading

Data buku beserta kategorinya diambil menggunakan:

```php
Buku::with('kategori')->latest()->paginate(10);
```

Eager loading digunakan untuk mengurangi query database berulang ketika menampilkan kategori dari banyak buku.

## Instalasi dan Menjalankan Aplikasi

1. Clone atau salin proyek ke komputer.
2. Buka terminal pada folder proyek.
3. Instal dependency:

```bash
composer install
npm install
```

4. Siapkan file environment:

```bash
copy .env.example .env
php artisan key:generate
```

5. Pastikan konfigurasi database SQLite sudah benar dan file `database/database.sqlite` tersedia.
6. Jalankan migrasi dan seeder:

```bash
php artisan migrate --seed
```

7. Jalankan aplikasi:

```bash
php artisan serve
```

8. Buka `http://127.0.0.1:8000` pada browser.

**Catatan:** Perintah migrasi dan seeder digunakan untuk menyiapkan database baru. Jangan menjalankannya sembarangan pada database yang berisi data penting.

## Data Awal

Seeder menyediakan 5 kategori dan 20 data buku untuk pengujian aplikasi.

## Pengujian

Pengujian dilakukan dengan memeriksa fitur CRUD buku dan kategori, validasi input, relasi antar-model, pagination, serta eager loading.

## Pengembang

Proyek praktikum Pemrograman dan Pengembangan Web — Institut Teknologi Del.
