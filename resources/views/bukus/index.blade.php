<x-layout>
    <div class="card">
        <h2>Katalog Buku & Analitik Perpustakaan</h2>
        <p>Kelola koleksi buku dan pantau statistik peminjaman di perpustakaan SIPUS-Del.</p>

        <!-- Kartu Statistik Analitik Perpustakaan (Query Builder DB::table) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin: 24px 0;">
            <div style="background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 8px; padding: 16px;">
                <span style="font-size: 12px; color: #6b7280; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Total Judul Buku</span>
                <div style="font-size: 28px; font-weight: bold; color: #4C1D95; margin-top: 6px;">{{ $stats['total_buku'] }}</div>
            </div>

            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px;">
                <span style="font-size: 12px; color: #6b7280; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Total Stok Tersedia</span>
                <div style="font-size: 28px; font-weight: bold; color: #1d4ed8; margin-top: 6px;">{{ $stats['total_stok'] }}</div>
            </div>

            <div style="background: #fefce8; border: 1px solid #fef08a; border-radius: 8px; padding: 16px;">
                <span style="font-size: 12px; color: #6b7280; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Sedang Dipinjam</span>
                <div style="font-size: 28px; font-weight: bold; color: #b45309; margin-top: 6px;">{{ $stats['total_dipinjam'] }}</div>
            </div>

            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px;">
                <span style="font-size: 12px; color: #6b7280; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Total Dikembalikan</span>
                <div style="font-size: 28px; font-weight: bold; color: #15803d; margin-top: 6px;">{{ $stats['total_kembali'] }}</div>
            </div>
        </div>

        <!-- Form Filter & Pencarian Dinamis -->
        <form method="GET" action="{{ route('bukus.index') }}" style="margin: 20px 0; padding: 16px; background: #faf5ff; border: 1px solid #ede9fe; border-radius: 8px;">
            <div style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 2; min-width: 220px;">
                    <label for="search" style="margin-top: 0; font-size: 13px; color: #374151;">Pencarian (Judul, Penulis, atau ISBN)</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Ketik kata kunci..."
                    >
                </div>

                <div style="flex: 1; min-width: 180px;">
                    <label for="kategori_id" style="margin-top: 0; font-size: 13px; color: #374151;">Kategori</label>
                    <select id="kategori_id" name="kategori_id">
                        <option value="">-- Semua Kategori --</option>
                        @foreach ($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="button" style="height: 42px;">
                        Cari
                    </button>

                    @if (request()->hasAny(['search', 'kategori_id']))
                        <a href="{{ route('bukus.index') }}" class="button button-secondary" style="height: 42px; line-height: 22px; box-sizing: border-box;">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <div style="margin: 20px 0;">
            <a href="{{ route('bukus.create') }}" class="button">
                + Tambah Buku
            </a>
        </div>

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
                        <th>Total Dipinjam</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($bukus as $buku)
                        <tr>
                            <td>{{ $bukus->firstItem() + $loop->index }}</td>
                            <td><code>{{ $buku->isbn }}</code></td>
                            <td>{{ $buku->judul }}</td>
                            <td>{{ $buku->penulis }}</td>
                            <td>{{ $buku->kategori->nama_kategori }}</td>
                            <td>{{ $buku->stok }}</td>
                            <td>
                                <span style="font-weight: bold; color: #4C1D95; background: #ede9fe; padding: 3px 8px; border-radius: 4px; font-size: 13px;">
                                    {{ $buku->peminjamans_count }} kali
                                </span>
                            </td>
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
            <p style="margin-top: 20px; color: #6b7280;">
                Tidak ada data buku yang sesuai dengan kriteria pencarian.
            </p>
        @endif
    </div>
</x-layout>
