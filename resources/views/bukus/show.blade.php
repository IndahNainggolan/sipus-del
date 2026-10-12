
<x-layout>
    <div class="card">
        <h2>Detail Buku</h2>

        <table>
            <tr>
                <th style="width: 200px;">ISBN</th>
                <td>{{ $buku->isbn }}</td>
            </tr>
            <tr>
                <th>Judul Buku</th>
                <td>{{ $buku->judul }}</td>
            </tr>
            <tr>
                <th>Penulis</th>
                <td>{{ $buku->penulis }}</td>
            </tr>
            <tr>
                <th>Penerbit</th>
                <td>{{ $buku->penerbit }}</td>
            </tr>
            <tr>
                <th>Tahun Terbit</th>
                <td>{{ $buku->tahun_terbit }}</td>
            </tr>
            <tr>
                <th>Kategori</th>
                <td>{{ $buku->kategori->nama_kategori }}</td>
            </tr>
            <tr>
                <th>Stok</th>
                <td>{{ $buku->stok }}</td>
            </tr>
            <tr>
                <th>Sinopsis</th>
                <td>{{ $buku->sinopsis ?: 'Belum ada sinopsis.' }}</td>
            </tr>
        </table>

        <div class="actions" style="margin-top: 24px;">
            <a href="{{ route('bukus.edit', $buku) }}"
               class="button">
                Edit Buku
            </a>

            <a href="{{ route('bukus.index') }}"
               class="button button-secondary">
                Kembali ke Daftar
            </a>
        </div>
    </div>
</x-layout>
