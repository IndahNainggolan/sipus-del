<x-layout>
    <div class="card">
        <h2>Sirkulasi Peminjaman Buku</h2>
        <p>Kelola pencatatan transaksi peminjaman dan pengembalian buku perpustakaan SIPUS-Del.</p>

        <a href="{{ route('peminjamans.create') }}" class="button">
            + Catat Peminjaman Baru
        </a>

        @if ($peminjamans->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Kode Pinjam</th>
                        <th>Judul Buku</th>
                        <th>Peminjam (NIM - Nama)</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($peminjamans as $peminjaman)
                        <tr>
                            <td>{{ $peminjamans->firstItem() + $loop->index }}</td>
                            <td><code>{{ $peminjaman->kode_pinjam }}</code></td>
                            <td>{{ $peminjaman->buku->judul ?? 'Buku tidak ditemukan' }}</td>
                            <td>
                                <strong>{{ $peminjaman->nim_peminjam }}</strong><br>
                                <span style="color: #4b5563;">{{ $peminjaman->nama_peminjam }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($peminjaman->tanggal_jatuh_tempo)->format('d M Y') }}</td>
                            <td>
                                {{ $peminjaman->tanggal_kembali ? \Carbon\Carbon::parse($peminjaman->tanggal_kembali)->format('d M Y') : '-' }}
                            </td>
                            <td>
                                @if ($peminjaman->status === 'dipinjam')
                                    <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-weight: bold; background: #fef3c7; color: #b45309;">
                                        Dipinjam
                                    </span>
                                @else
                                    <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-weight: bold; background: #dcfce7; color: #15803d;">
                                        Dikembalikan
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if ($peminjaman->status === 'dipinjam')
                                    <form action="{{ route('peminjamans.kembalikan', $peminjaman) }}"
                                          method="POST"
                                          onsubmit="return confirm('Konfirmasi pengembalian buku ini?')">
                                        @csrf
                                        <button type="submit" class="button" style="background: #0284c7; padding: 6px 12px; font-size: 13px;">
                                            Kembalikan
                                        </button>
                                    </form>
                                @else
                                    <span style="color: #9ca3af; font-size: 13px;">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                {{ $peminjamans->links() }}
            </div>
        @else
            <p style="margin-top: 20px;">Belum ada riwayat transaksi peminjaman.</p>
        @endif
    </div>
</x-layout>
