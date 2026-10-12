
<x-layout>
    <div class="card">
        <h2>Edit Kategori</h2>
        <p>Ubah kode atau nama kategori buku.</p>

        <form action="{{ route('kategoris.update', $kategori) }}" method="POST">
            @csrf
            @method('PUT')

            <label for="kode_kategori">Kode Kategori</label>
            <input
                type="text"
                id="kode_kategori"
                name="kode_kategori"
                value="{{ old('kode_kategori', $kategori->kode_kategori) }}"
                required
            >

            <label for="nama_kategori">Nama Kategori</label>
            <input
                type="text"
                id="nama_kategori"
                name="nama_kategori"
                value="{{ old('nama_kategori', $kategori->nama_kategori) }}"
                required
            >

            <div class="actions" style="margin-top: 24px;">
                <button type="submit" class="button">
                    Simpan Perubahan
                </button>

                <a href="{{ route('kategoris.index') }}"
                   class="button button-secondary">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-layout>
