
<x-layout>
    <div class="card">
        <h2>Daftar Buku</h2>
        <p>Kelola koleksi buku yang tersedia di perpustakaan SIPUS-Del.</p>

        <a href="{{ route('bukus.create') }}" class="button">
            + Tambah Buku
        </a>

        @if ($bukus->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>ISBN</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($bukus as $buku)
                        <tr>
                            <td>{{ $bukus->firstItem() + $loop->index }}</td>
                            <td>{{ $buku->isbn }}</td>
                            <td>{{ $buku->judul }}</td>
                            <td>{{ $buku->penulis }}</td>
                            <td>{{ $buku->kategori->nama_kategori }}</td>
                            <td>{{ $buku->stok }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('bukus.show', $buku) }}"
                                       class="button">
                                        Detail
                                    </a>

                                    <a href="{{ route('bukus.edit', $buku) }}"
                                       class="button button-secondary">
                                        Edit
                                    </a>

                                    <form action="{{ route('bukus.destroy', $buku) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus buku ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="button button-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                {{ $bukus->links() }}
            </div>
        @else
            <p>Belum ada data buku. Silakan tambahkan buku terlebih dahulu.</p>
        @endif
    </div>
</x-layout>
