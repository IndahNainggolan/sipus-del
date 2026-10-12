
<x-layout>
    <div class="card">
        <h2>Edit Buku</h2>
        <p>Ubah informasi buku sesuai data terbaru.</p>

        <form action="{{ route('bukus.update', $buku) }}" method="POST">
            @csrf
            @method('PUT')

            <label for="isbn">ISBN</label>
            <input
                type="text"
                id="isbn"
                name="isbn"
                value="{{ old('isbn', $buku->isbn) }}"
                required
            >

            <label for="judul">Judul Buku</label>
            <input
                type="text"
                id="judul"
                name="judul"
                value="{{ old('judul', $buku->judul) }}"
                minlength="5"
                required
            >

            <label for="penulis">Penulis</label>
            <input
                type="text"
                id="penulis"
                name="penulis"
                value="{{ old('penulis', $buku->penulis) }}"
                required
            >

            <label for="penerbit">Penerbit</label>
            <input
                type="text"
                id="penerbit"
                name="penerbit"
                value="{{ old('penerbit', $buku->penerbit) }}"
                required
            >

            <label for="tahun_terbit">Tahun Terbit</label>
            <input
                type="number"
                id="tahun_terbit"
                name="tahun_terbit"
                value="{{ old('tahun_terbit', $buku->tahun_terbit) }}"
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
                        @selected(old('kategori_id', $buku->kategori_id) == $kategori->id)
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
                value="{{ old('stok', $buku->stok) }}"
                min="0"
                required
            >

            <label for="sinopsis">Sinopsis</label>
            <textarea id="sinopsis" name="sinopsis" rows="5">{{ old('sinopsis', $buku->sinopsis) }}</textarea>

            <div class="actions" style="margin-top: 24px;">
                <button type="submit" class="button">
                    Simpan Perubahan
                </button>

                <a href="{{ route('bukus.index') }}"
                   class="button button-secondary">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-layout>
