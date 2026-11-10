<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

class PeminjamanController extends Controller
{
    public function index(Request $request): View
    {
        $peminjamans = Peminjaman::with('buku')
            ->latest()
            ->paginate(10);

        return view('peminjamans.index', compact('peminjamans'));
    }

    public function create(Request $request): View
    {
        $selectedBukuId = $request->query('buku_id');
        $bukus = Buku::orderBy('judul')->get();

        return view('peminjamans.create', compact('bukus', 'selectedBukuId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'buku_id' => 'required|exists:bukus,id',
            'nim_peminjam' => 'required|string|min:4|max:20',
            'nama_peminjam' => 'required|string|min:3|max:150',
            'tanggal_pinjam' => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:tanggal_pinjam',
        ]);

        $nim = strtoupper(trim($validated['nim_peminjam']));
        $bukuId = (int) $validated['buku_id'];

        try {
            DB::transaction(function () use ($validated, $nim, $bukuId) {
                // 1. Kunci baris buku menggunakan lockForUpdate() (Pessimistic Locking)
                $buku = Buku::where('id', $bukuId)->lockForUpdate()->firstOrFail();

                // 2. Cek ketersediaan stok buku (> 0)
                if ($buku->stok <= 0) {
                    throw new \RuntimeException("Buku '{$buku->judul}' tidak dapat dipinjam karena stok habis (stok = 0).");
                }

                // 3. Cegah NIM yang sama meminjam buku yang sama yang belum dikembalikan
                $masihDipinjam = Peminjaman::where('buku_id', $buku->id)
                    ->where('nim_peminjam', $nim)
                    ->where('status', 'dipinjam')
                    ->lockForUpdate()
                    ->exists();

                if ($masihDipinjam) {
                    throw new \RuntimeException("Mahasiswa dengan NIM {$nim} masih memiliki peminjaman aktif untuk buku ini yang belum dikembalikan.");
                }

                // 4. Kurangi stok buku secara atomik
                $buku->decrement('stok');

                // 5. Buat kode transaksi peminjaman unik
                $kodePinjam = 'PJ-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                // 6. Catat transaksi ke tabel peminjamans
                Peminjaman::create([
                    'kode_pinjam' => $kodePinjam,
                    'buku_id' => $buku->id,
                    'nim_peminjam' => $nim,
                    'nama_peminjam' => trim($validated['nama_peminjam']),
                    'tanggal_pinjam' => $validated['tanggal_pinjam'],
                    'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
                    'status' => 'dipinjam',
                ]);
            }, 3);

            return redirect()
                ->route('peminjamans.index')
                ->with('success', 'Transaksi peminjaman buku berhasil diproses secara atomik.');
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION - PEMINJAMAN]: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan basis data saat memproses peminjaman.');
        }
    }

    public function kembalikan(Peminjaman $peminjaman): RedirectResponse
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                // 1. Kunci baris peminjaman menggunakan lockForUpdate()
                $lockedPeminjaman = Peminjaman::where('id', $peminjaman->id)->lockForUpdate()->firstOrFail();

                if ($lockedPeminjaman->status === 'dikembalikan') {
                    throw new \RuntimeException('Buku untuk transaksi peminjaman ini sudah dikembalikan sebelumnya.');
                }

                // 2. Kunci baris buku terkait
                $buku = Buku::where('id', $lockedPeminjaman->buku_id)->lockForUpdate()->firstOrFail();

                // 3. Ubah status menjadi dikembalikan dan isi tanggal kembali
                $lockedPeminjaman->update([
                    'status' => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);

                // 4. Kembalikan stok buku secara atomik
                $buku->increment('stok');
            }, 3);

            return redirect()
                ->route('peminjamans.index')
                ->with('success', 'Buku berhasil dikembalikan dan stok telah diperbarui.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION - PENGEMBALIAN]: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan basis data saat memproses pengembalian buku.');
        }
    }
}
