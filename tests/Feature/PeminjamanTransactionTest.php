<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PeminjamanTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected Buku $bukuAdaStok;
    protected Buku $bukuHabisStok;
    protected User $pustakawan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pustakawan = User::create([
            'name' => 'Pustakawan Uji Transaksi',
            'email' => 'pustakawan.transaksi@del.ac.id',
            'password' => Hash::make('password123'),
        ]);

        $kategori = Kategori::create([
            'kode_kategori' => 'KTG999',
            'nama_kategori' => 'Kategori Uji',
        ]);

        $this->bukuAdaStok = Buku::create([
            'isbn' => '9781234567890',
            'judul' => 'Buku Uji Stok Tersedia',
            'penulis' => 'Penulis Uji',
            'penerbit' => 'Penerbit Uji',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id,
            'stok' => 3,
            'sinopsis' => 'Sinopsis uji',
        ]);

        $this->bukuHabisStok = Buku::create([
            'isbn' => '9781234567891',
            'judul' => 'Buku Uji Stok Nol',
            'penulis' => 'Penulis Uji 2',
            'penerbit' => 'Penerbit Uji 2',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id,
            'stok' => 0,
            'sinopsis' => 'Sinopsis uji',
        ]);
    }

    public function test_peminjaman_berhasil_dan_stok_berkurang_secara_atomik(): void
    {
        $response = $this->actingAs($this->pustakawan)
            ->post(route('peminjamans.store'), [
                'buku_id' => $this->bukuAdaStok->id,
                'nim_peminjam' => '12S24001',
                'nama_peminjam' => 'Mahasiswa Uji',
                'tanggal_pinjam' => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            ]);

        $response->assertRedirect(route('peminjamans.index'));
        $response->assertSessionHas('success');

        // Verifikasi stok berkurang dari 3 menjadi 2
        $this->assertEquals(2, $this->bukuAdaStok->fresh()->stok);

        // Verifikasi data peminjaman tercatat
        $this->assertDatabaseHas('peminjamans', [
            'buku_id' => $this->bukuAdaStok->id,
            'nim_peminjam' => '12S24001',
            'status' => 'dipinjam',
        ]);
    }

    public function test_peminjaman_gagal_dan_rollback_jika_stok_nol(): void
    {
        $response = $this->actingAs($this->pustakawan)
            ->post(route('peminjamans.store'), [
                'buku_id' => $this->bukuHabisStok->id,
                'nim_peminjam' => '12S24002',
                'nama_peminjam' => 'Mahasiswa Uji 2',
                'tanggal_pinjam' => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            ]);

        $response->assertSessionHas('error');
        // Stok tetap 0
        $this->assertEquals(0, $this->bukuHabisStok->fresh()->stok);
        // Tidak ada data peminjaman yang tersimpan
        $this->assertDatabaseMissing('peminjamans', [
            'buku_id' => $this->bukuHabisStok->id,
            'nim_peminjam' => '12S24002',
        ]);
    }

    public function test_peminjaman_gagal_jika_nim_sama_meminjam_buku_sama_belum_kembali(): void
    {
        // Peminjaman pertama berhasil
        $this->actingAs($this->pustakawan)->post(route('peminjamans.store'), [
            'buku_id' => $this->bukuAdaStok->id,
            'nim_peminjam' => '12S24003',
            'nama_peminjam' => 'Mahasiswa Uji 3',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
        ]);

        $this->assertEquals(2, $this->bukuAdaStok->fresh()->stok);

        // Peminjaman kedua oleh NIM sama untuk buku sama harus ditolak
        $response = $this->actingAs($this->pustakawan)->post(route('peminjamans.store'), [
            'buku_id' => $this->bukuAdaStok->id,
            'nim_peminjam' => '12S24003',
            'nama_peminjam' => 'Mahasiswa Uji 3',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertSessionHas('error');
        // Stok tetap 2 (tidak berkurang lagi)
        $this->assertEquals(2, $this->bukuAdaStok->fresh()->stok);
    }

    public function test_pengembalian_buku_berhasil_dan_stok_bertambah_secara_atomik(): void
    {
        // Lakukan peminjaman awal
        $this->actingAs($this->pustakawan)->post(route('peminjamans.store'), [
            'buku_id' => $this->bukuAdaStok->id,
            'nim_peminjam' => '12S24004',
            'nama_peminjam' => 'Mahasiswa Uji 4',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
        ]);

        $peminjaman = Peminjaman::where('nim_peminjam', '12S24004')->firstOrFail();
        $this->assertEquals(2, $this->bukuAdaStok->fresh()->stok);

        // Lakukan pengembalian
        $response = $this->actingAs($this->pustakawan)->post(route('peminjamans.kembalikan', $peminjaman));
        $response->assertSessionHas('success');

        // Status berubah menjadi dikembalikan
        $peminjamanFresh = $peminjaman->fresh();
        $this->assertEquals('dikembalikan', $peminjamanFresh->status);
        $this->assertNotNull($peminjamanFresh->tanggal_kembali);

        // Stok buku bertambah kembali menjadi 3
        $this->assertEquals(3, $this->bukuAdaStok->fresh()->stok);
    }

    public function test_pengembalian_buku_yang_sudah_dikembalikan_ditolak(): void
    {
        $peminjaman = Peminjaman::create([
            'kode_pinjam' => 'PJ-TEST-001',
            'buku_id' => $this->bukuAdaStok->id,
            'nim_peminjam' => '12S24005',
            'nama_peminjam' => 'Mahasiswa Uji 5',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'tanggal_kembali' => now()->toDateString(),
            'status' => 'dikembalikan',
        ]);

        $stokAwal = $this->bukuAdaStok->fresh()->stok;

        $response = $this->actingAs($this->pustakawan)->post(route('peminjamans.kembalikan', $peminjaman));
        $response->assertSessionHas('error');

        // Stok tidak berubah
        $this->assertEquals($stokAwal, $this->bukuAdaStok->fresh()->stok);
    }

    public function test_tamu_tidak_dapat_melakukan_transaksi_peminjaman_dan_diredirect_ke_login(): void
    {
        $response = $this->post(route('peminjamans.store'), [
            'buku_id' => $this->bukuAdaStok->id,
            'nim_peminjam' => '12S24099',
            'nama_peminjam' => 'Tamu Coba Pinjam',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect(route('login'));
        $this->assertEquals(3, $this->bukuAdaStok->fresh()->stok);
    }
}
