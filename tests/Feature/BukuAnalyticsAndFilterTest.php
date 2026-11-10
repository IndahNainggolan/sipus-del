<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BukuAnalyticsAndFilterTest extends TestCase
{
    use RefreshDatabase;

    protected Kategori $kategori1;
    protected Kategori $kategori2;
    protected Buku $bukuLaravel;
    protected Buku $bukuPython;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kategori1 = Kategori::create([
            'kode_kategori' => 'KTG-WEB',
            'nama_kategori' => 'Pemrograman Web',
        ]);

        $this->kategori2 = Kategori::create([
            'kode_kategori' => 'KTG-AI',
            'nama_kategori' => 'Kecerdasan Buatan',
        ]);

        $this->bukuLaravel = Buku::create([
            'isbn' => '9781111111111',
            'judul' => 'Mastering Laravel Framework',
            'penulis' => 'Taylor Otwell',
            'penerbit' => 'O\'Reilly',
            'tahun_terbit' => 2024,
            'kategori_id' => $this->kategori1->id,
            'stok' => 10,
            'sinopsis' => 'Buku panduan lengkap Laravel 11/13.',
        ]);

        $this->bukuPython = Buku::create([
            'isbn' => '9782222222222',
            'judul' => 'Deep Learning with Python',
            'penulis' => 'Francois Chollet',
            'penerbit' => 'Manning',
            'tahun_terbit' => 2023,
            'kategori_id' => $this->kategori2->id,
            'stok' => 5,
            'sinopsis' => 'Panduan deep learning Keras dan PyTorch.',
        ]);

        // Sampel transaksi peminjaman: 1 berstatus dipinjam, 1 berstatus dikembalikan
        Peminjaman::create([
            'kode_pinjam' => 'PJ-TEST-001',
            'buku_id' => $this->bukuLaravel->id,
            'nim_peminjam' => '12S24001',
            'nama_peminjam' => 'Mahasiswa Web',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'dipinjam',
        ]);

        Peminjaman::create([
            'kode_pinjam' => 'PJ-TEST-002',
            'buku_id' => $this->bukuLaravel->id,
            'nim_peminjam' => '12S24002',
            'nama_peminjam' => 'Mahasiswa Web 2',
            'tanggal_pinjam' => now()->subDays(10)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
            'tanggal_kembali' => now()->subDays(1)->toDateString(),
            'status' => 'dikembalikan',
        ]);
    }

    public function test_katalog_menampilkan_statistik_db_table_dengan_akurat(): void
    {
        $response = $this->get(route('bukus.index'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function (array $stats) {
            return $stats['total_buku'] === 2
                && $stats['total_stok'] === 15
                && $stats['total_dipinjam'] === 1
                && $stats['total_kembali'] === 1;
        });

        // Verifikasi angka statistik tercetak di halaman
        $response->assertSee('Total Judul Buku');
        $response->assertSee('Total Stok Tersedia');
        $response->assertSee('Sedang Dipinjam');
        $response->assertSee('Total Dikembalikan');
    }

    public function test_katalog_memuat_with_count_peminjamans(): void
    {
        $response = $this->get(route('bukus.index'));

        $response->assertStatus(200);
        $response->assertViewHas('bukus', function ($bukus) {
            $laravel = $bukus->firstWhere('id', $this->bukuLaravel->id);
            $python = $bukus->firstWhere('id', $this->bukuPython->id);

            // Buku Laravel dipinjam 2 kali (1 dipinjam + 1 dikembalikan)
            // Buku Python belum pernah dipinjam (0 kali)
            return $laravel->peminjamans_count === 2
                && $python->peminjamans_count === 0;
        });

        $response->assertSee('2 kali');
        $response->assertSee('0 kali');
    }

    public function test_filter_pencarian_judul_penulis_isbn(): void
    {
        // 1. Cari berdasarkan judul "Laravel"
        $responseJudul = $this->get(route('bukus.index', ['search' => 'Laravel']));
        $responseJudul->assertSee('Mastering Laravel Framework');
        $responseJudul->assertDontSee('Deep Learning with Python');

        // 2. Cari berdasarkan penulis "Chollet"
        $responsePenulis = $this->get(route('bukus.index', ['search' => 'Chollet']));
        $responsePenulis->assertSee('Deep Learning with Python');
        $responsePenulis->assertDontSee('Mastering Laravel Framework');

        // 3. Cari berdasarkan ISBN "9781111111111"
        $responseIsbn = $this->get(route('bukus.index', ['search' => '9781111111111']));
        $responseIsbn->assertSee('Mastering Laravel Framework');
        $responseIsbn->assertDontSee('Deep Learning with Python');
    }

    public function test_filter_berdasarkan_kategori(): void
    {
        // Filter kategori AI
        $response = $this->get(route('bukus.index', ['kategori_id' => $this->kategori2->id]));

        $response->assertSee('Deep Learning with Python');
        $response->assertDontSee('Mastering Laravel Framework');
    }

    public function test_paginasi_mempertahankan_query_string(): void
    {
        // Tambahkan 15 buku tambahan agar paginasi aktif (> 10 item)
        for ($i = 1; $i <= 15; $i++) {
            Buku::create([
                'isbn' => "97833333333{$i}",
                'judul' => "Buku Ekstra {$i}",
                'penulis' => "Penulis Ekstra {$i}",
                'penerbit' => "Penerbit Ekstra",
                'tahun_terbit' => 2024,
                'kategori_id' => $this->kategori1->id,
                'stok' => 5,
                'sinopsis' => 'Ekstra',
            ]);
        }

        $response = $this->get(route('bukus.index', [
            'search' => 'Ekstra',
            'kategori_id' => $this->kategori1->id,
        ]));

        $response->assertStatus(200);

        // Verifikasi link halaman berikutnya (page=2) membawa query string 'search=Ekstra' dan 'kategori_id'
        $content = $response->getContent();
        $this->assertStringContainsString('search=Ekstra', $content);
        $this->assertStringContainsString('kategori_id=' . $this->kategori1->id, $content);
        $this->assertStringContainsString('page=2', $content);
    }
}
