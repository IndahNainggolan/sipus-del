
<x-layout>
    <div class="card">
        <h2>Detail Kategori</h2>

        <table>
            <tr>
                <th style="width: 200px;">Kode Kategori</th>
                <td>{{ $kategori->kode_kategori }}</td>
            </tr>
            <tr>
                <th>Nama Kategori</th>
                <td>{{ $kategori->nama_kategori }}</td>
            </tr>
            <tr>
                <th>Jumlah Buku</th>
                <td>{{ $kategori->bukus->count() }}</td>
            </tr>
        </table>

        <h3>Daftar Buku dalam Kategori Ini</h3>

        @forelse ($kategori->bukus as $buku)
            <p>
                {{ $buku->judul }}
                <span style="color: #6b7280;">
                    (Stok: {{ $buku->stok }})
                </span>
            </p>
        @empty
            <p>Belum ada buku dalam kategori ini.</p>
        @endforelse

        <div class="actions" style="margin-top: 24px;">
            <a href="{{ route('kategoris.edit', $kategori) }}"
               class="button">
                Edit Kategori
            </a>

            <a href="{{ route('kategoris.index') }}"
               class="button button-secondary">
                Kembali ke Daftar
            </a>
        </div>
    </div>
</x-layout>
