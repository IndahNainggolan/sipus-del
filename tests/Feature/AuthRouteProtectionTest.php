<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthRouteProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $pustakawan;
    protected Kategori $kategori;
    protected Buku $buku;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pustakawan = User::create([
            'name' => 'Pustakawan Uji Proteksi',
            'email' => 'pustakawan.proteksi@del.ac.id',
            'password' => Hash::make('password123'),
        ]);

        $this->kategori = Kategori::create([
            'kode_kategori' => 'KTG-PROT',
            'nama_kategori' => 'Kategori Proteksi',
        ]);

        $this->buku = Buku::create([
            'isbn' => '9789999999999',
            'judul' => 'Buku Uji Proteksi Rute',
            'penulis' => 'Penulis Proteksi',
            'penerbit' => 'Penerbit Proteksi',
            'tahun_terbit' => 2024,
            'kategori_id' => $this->kategori->id,
            'stok' => 5,
            'sinopsis' => 'Sinopsis proteksi',
        ]);
    }

    public function test_halaman_baca_katalog_dan_detail_tetap_dapat_diakses_publik(): void
    {
        // 1. Root redirect
        $this->get('/')->assertRedirect(route('bukus.index'));

        // 2. Katalog buku publik
        $this->get(route('bukus.index'))->assertStatus(200);

        // 3. Detail buku publik
        $this->get(route('bukus.show', $this->buku))->assertStatus(200);

        // 4. Daftar kategori publik
        $this->get(route('kategoris.index'))->assertStatus(200);

        // 5. Detail kategori publik
        $this->get(route('kategoris.show', $this->kategori))->assertStatus(200);

        // 6. Riwayat sirkulasi peminjaman publik
        $this->get(route('peminjamans.index'))->assertStatus(200);
    }

    public function test_rute_mutasi_buku_dibatasi_untuk_tamu_dan_dapat_diakses_pustakawan(): void
    {
        // Tamu ditolak
        $this->get(route('bukus.create'))->assertRedirect(route('login'));
        $this->post(route('bukus.store'), [])->assertRedirect(route('login'));
        $this->get(route('bukus.edit', $this->buku))->assertRedirect(route('login'));
        $this->put(route('bukus.update', $this->buku), [])->assertRedirect(route('login'));
        $this->delete(route('bukus.destroy', $this->buku))->assertRedirect(route('login'));

        // Pustakawan terautentikasi dapat mengakses
        $this->actingAs($this->pustakawan)
            ->get(route('bukus.create'))
            ->assertStatus(200);

        $this->actingAs($this->pustakawan)
            ->get(route('bukus.edit', $this->buku))
            ->assertStatus(200);
    }

    public function test_rute_mutasi_kategori_dibatasi_untuk_tamu_dan_dapat_diakses_pustakawan(): void
    {
        // Tamu ditolak
        $this->get(route('kategoris.create'))->assertRedirect(route('login'));
        $this->post(route('kategoris.store'), [])->assertRedirect(route('login'));
        $this->get(route('kategoris.edit', $this->kategori))->assertRedirect(route('login'));
        $this->put(route('kategoris.update', $this->kategori), [])->assertRedirect(route('login'));
        $this->delete(route('kategoris.destroy', $this->kategori))->assertRedirect(route('login'));

        // Pustakawan terautentikasi dapat mengakses
        $this->actingAs($this->pustakawan)
            ->get(route('kategoris.create'))
            ->assertStatus(200);

        $this->actingAs($this->pustakawan)
            ->get(route('kategoris.edit', $this->kategori))
            ->assertStatus(200);
    }

    public function test_rute_mutasi_peminjaman_dibatasi_untuk_tamu_dan_dapat_diakses_pustakawan(): void
    {
        // Tamu ditolak saat mengakses form peminjaman
        $this->get(route('peminjamans.create'))->assertRedirect(route('login'));

        // Pustakawan terautentikasi dapat membuka form peminjaman
        $this->actingAs($this->pustakawan)
            ->get(route('peminjamans.create'))
            ->assertStatus(200);
    }
}
