
<x-layout>
    <div class="card">
        <h2>Tambah Kategori</h2>
        <p>Masukkan kode dan nama kategori buku.</p>

        <form action="{{ route('kategoris.store') }}" method="POST">
            @csrf

            <label for="kode_kategori">Kode Kategori</label>
            <input
                type="text"
                id="kode_kategori"
                name="kode_kategori"
                value="{{ old('kode_kategori') }}"
                placeholder="Contoh: KTG001"
                required
            >

            <label for="nama_kategori">Nama Kategori</label>
            <input
                type="text"
                id="nama_kategori"
                name="nama_kategori"
                value="{{ old('nama_kategori') }}"
                placeholder="Contoh: Teknologi"
                required
            >

            <div class="actions" style="margin-top: 24px;">
                <button type="submit" class="button">
                    Simpan Kategori
                </button>

                <a href="{{ route('kategoris.index') }}"
                   class="button button-secondary">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</x-layout>
