<x-layout>
    <div class="card" style="max-width: 480px; margin: 40px auto;">
        <h2>Login Pustakawan</h2>
        <p>Silakan masuk untuk mengelola koleksi buku, kategori, dan sirkulasi peminjaman SIPUS-Del.</p>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <label for="email">Alamat Email Pustakawan</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $lastEmail ?? '') }}"
                placeholder="nama@del.ac.id"
                required
                autofocus
            >

            <label for="password">Kata Sandi</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Masukkan kata sandi"
                required
            >

            <div style="margin-top: 16px; display: flex; align-items: center; gap: 8px;">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    value="1"
                    {{ old('remember') ? 'checked' : '' }}
                    style="width: auto; margin: 0; cursor: pointer;"
                >
                <label for="remember" style="margin: 0; font-weight: normal; cursor: pointer; font-size: 14px;">
                    Ingat Saya di Perangkat Ini
                </label>
            </div>

            <div class="actions" style="margin-top: 24px;">
                <button type="submit" class="button" style="width: 100%; text-align: center;">
                    Masuk sebagai Pustakawan
                </button>
            </div>
        </form>
    </div>
</x-layout>
