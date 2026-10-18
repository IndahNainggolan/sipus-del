<x-layout>
    <div class="card">
        <h2>Catat Peminjaman Buku</h2>
        <p>Isi formulir peminjaman buku perpustakaan SIPUS-Del. Transaksi diproses secara atomik.</p>

        <form action="{{ route('peminjamans.store') }}" method="POST">
            @csrf

            <label for="buku_id">Pilih Buku</label>
            <select id="buku_id" name="buku_id" required>
                <option value="">-- Pilih Buku yang Ingin Dipinjam --</option>
                @foreach ($bukus as $buku)
                    <option value="{{ $buku->id }}"
                        {{ (old('buku_id', $selectedBukuId) == $buku->id) ? 'selected' : '' }}
                        {{ $buku->stok <= 0 ? 'style=color:#dc2626;' : '' }}>
                        {{ $buku->judul }} — (Tersedia: {{ $buku->stok }} eks)
                        {{ $buku->stok <= 0 ? ' [STOK HABIS]' : '' }}
                    </option>
                @endforeach
            </select>

            <label for="nim_peminjam">NIM Peminjam</label>
            <input
                type="text"
                id="nim_peminjam"
                name="nim_peminjam"
                value="{{ old('nim_peminjam') }}"
                placeholder="Contoh: 12S24001"
                required
            >

            <label for="nama_peminjam">Nama Lengkap Mahasiswa</label>
            <input
                type="text"
                id="nama_peminjam"
                name="nama_peminjam"
                value="{{ old('nama_peminjam') }}"
                placeholder="Contoh: Budi Santoso"
                required
            >

            <label for="tanggal_pinjam">Tanggal Peminjaman</label>
            <input
                type="date"
                id="tanggal_pinjam"
                name="tanggal_pinjam"
                value="{{ old('tanggal_pinjam', date('Y-m-d')) }}"
                required
            >

            <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo</label>
            <input
                type="date"
                id="tanggal_jatuh_tempo"
                name="tanggal_jatuh_tempo"
                value="{{ old('tanggal_jatuh_tempo', date('Y-m-d', strtotime('+7 days'))) }}"
                required
            >

            <div class="actions" style="margin-top: 24px;">
                <button type="submit" class="button">
                    Proses Peminjaman
                </button>

                <a href="{{ route('peminjamans.index') }}" class="button button-secondary">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-layout>
