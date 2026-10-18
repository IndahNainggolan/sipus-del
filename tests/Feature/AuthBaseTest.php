<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthBaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $pustakawan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pustakawan = User::create([
            'name' => 'Pustakawan Uji Del',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_halaman_login_dapat_diakses_oleh_tamu(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Login Pustakawan');
        $response->assertSee('Masuk sebagai Pustakawan');
    }

    public function test_login_berhasil_meregenerasi_sesi_dan_mengatur_cookie(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'password123',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('bukus.index'));
        $this->assertAuthenticatedAs($this->pustakawan);

        // Verifikasi Session state
        $response->assertSessionHas('login_timestamp');

        // Verifikasi Cookie del_last_user_email
        $response->assertCookie('del_last_user_email', 'pustakawan@del.ac.id');
    }

    public function test_login_gagal_dengan_kredensial_salah(): void
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'password_salah',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Kredensial login tidak valid.');
        $this->assertGuest();
    }

    public function test_logout_berhasil_dan_menginvalidasi_sesi(): void
    {
        $response = $this->actingAs($this->pustakawan)
            ->post(route('logout'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        $this->assertGuest();
    }

    public function test_pengguna_terautentikasi_diredirect_saat_akses_halaman_login(): void
    {
        $response = $this->actingAs($this->pustakawan)
            ->get(route('login'));

        // Middleware guest mengalihkan pengguna yang sudah login
        $response->assertRedirect();
    }
}
