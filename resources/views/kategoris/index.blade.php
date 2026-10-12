
<x-layout>
    <div class="card">
        <h2>Daftar Kategori Buku</h2>
        <p>Kelola kategori koleksi buku perpustakaan SIPUS-Del.</p>

        <a href="{{ route('kategoris.create') }}" class="button">
            + Tambah Kategori
        </a>

        @if ($kategoris->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Kode Kategori</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Buku</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($kategoris as $kategori)
                        <tr>
                            <td>{{ $kategoris->firstItem() + $loop->index }}</td>
                            <td>{{ $kategori->kode_kategori }}</td>
                            <td>{{ $kategori->nama_kategori }}</td>
                            <td>{{ $kategori->bukus()->count() }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('kategoris.show', $kategori) }}"
                                       class="button">
                                        Detail
                                    </a>

                                    <a href="{{ route('kategoris.edit', $kategori) }}"
                                       class="button button-secondary">
                                        Edit
                                    </a>

                                    <form action="{{ route('kategoris.destroy', $kategori) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
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
                {{ $kategoris->links() }}
            </div>
        @else
            <p>Belum ada kategori. Silakan tambahkan kategori terlebih dahulu.</p>
        @endif
    </div>
</x-layout>
