
<x-layout>
    <div class="card">
        <h2>Tambah Buku</h2>
        <p>Isi informasi buku yang ingin ditambahkan ke perpustakaan.</p>

        <form action="{{ route('bukus.store') }}" method="POST">
            @csrf

            <label for="isbn">ISBN</label>
            <input
                type="text"
                id="isbn"
                name="isbn"
                value="{{ old('isbn') }}"
                required
            >

            <label for="judul">Judul Buku</label>
            <input
                type="text"
                id="judul"
                name="judul"
                value="{{ old('judul') }}"
                minlength="5"
                required
            >

            <label for="penulis">Penulis</label>
            <input
                type="text"
                id="penulis"
                name="penulis"
                value="{{ old('penulis') }}"
                required
            >

            <label for="penerbit">Penerbit</label>
            <input
                type="text"
                id="penerbit"
                name="penerbit"
                value="{{ old('penerbit') }}"
                required
            >

            <label for="tahun_terbit">Tahun Terbit</label>
            <input
                type="number"
                id="tahun_terbit"
                name="tahun_terbit"
                value="{{ old('tahun_terbit') }}"
                min="1000"
                max="{{ date('Y') }}"
                required
            >

            <label for="kategori_id">Kategori</label>
            <select id="kategori_id" name="kategori_id" required>
                <option value="">-- Pilih Kategori --</option>

                @foreach ($kategoris as $kategori)
                    <option
                        value="{{ $kategori->id }}"
                        @selected(old('kategori_id') == $kategori->id)
                    >
                        {{ $kategori->nama_kategori }}
                    </option>
                @endforeach
            </select>

            <label for="stok">Stok Buku</label>
            <input
                type="number"
                id="stok"
                name="stok"
                value="{{ old('stok', 0) }}"
                min="0"
                required
            >

            <label for="sinopsis">Sinopsis</label>
            <textarea
                id="sinopsis"
                name="sinopsis"
                rows="5"
            >{{ old('sinopsis') }}</textarea>

            <div class="actions" style="margin-top: 24px;">
                <button type="submit" class="button">
                    Simpan Buku
                </button>

                <a href="{{ route('bukus.index') }}"
                   class="button button-secondary">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</x-layout>
